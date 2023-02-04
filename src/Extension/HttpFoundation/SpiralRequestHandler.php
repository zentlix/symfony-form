<?php

declare(strict_types=1);

namespace Spiral\Symfony\Form\HttpFoundation;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Http\Request\InputManager;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\RequestHandlerInterface;
use Symfony\Component\Form\Util\ServerParams;

class SpiralRequestHandler implements RequestHandlerInterface
{
    private ServerParams $serverParams;

    public function __construct(ServerParams $serverParams = null)
    {
        $this->serverParams = $serverParams ?? new ServerParams();
    }

    public function handleRequest(FormInterface $form, mixed $request = null): void
    {
        if (!$request instanceof InputManager) {
            throw new UnexpectedTypeException($request, InputManager::class);
        }

        $name = $form->getName();
        $method = $form->getConfig()->getMethod();

        if ($method !== $request->method()) {
            return;
        }

        // For request methods that must not have a request body we fetch data
        // from the query string. Otherwise we look for data in the request body.
        if ('GET' === $method || 'HEAD' === $method || 'TRACE' === $method) {
            if ('' === $name) {
                $data = $request->query->all();
            } else {
                // Don't submit GET requests if the form's name does not exist
                // in the request
                if (!$request->query->has($name)) {
                    return;
                }

                $data = $request->query->all()[$name];
            }
        } else {
            // Mark the form with an error if the uploaded size was too large
            // This is done here and not in FormValidator because $_POST is
            // empty when that error occurs. Hence the form is never submitted.
            if ($this->serverParams->hasPostMaxSizeBeenExceeded()) {
                // Submit the form, but don't clear the default values
                $form->submit(null, false);

                $form->addError(new FormError(
                    $form->getConfig()->getOption('upload_max_size_message')(),
                    null,
                    ['{{ max }}' => $this->serverParams->getNormalizedIniPostMaxSize()]
                ));

                return;
            }

            if ('' === $name) {
                $params = $request->data->all();
                $files = $request->files->all();
            } elseif ($request->data->has($name) || $request->files->has($name)) {
                $default = $form->getConfig()->getCompound() ? [] : null;
                $params = $request->data->all()[$name] ?? $default;
                $files = $request->files->get($name, $default);
            } else {
                // Don't submit the form if it is not present in the request
                return;
            }

            if (\is_array($params) && \is_array($files)) {
                $data = array_replace_recursive($params, $files);
            } else {
                $data = $params ?: $files;
            }
        }

        // Don't auto-submit the form unless at least one field is present.
        if ('' === $name && \count(array_intersect_key($data ?? [], $form->all())) <= 0) {
            return;
        }

        $form->submit($data, 'PATCH' !== $method);
    }

    public function isFileUpload(mixed $data): bool
    {
        return $data instanceof UploadedFileInterface;
    }

    public function getUploadFileError(mixed $data): ?int
    {
        if (!$data instanceof UploadedFileInterface) {
            return null;
        }

        return $data->getError();
    }
}

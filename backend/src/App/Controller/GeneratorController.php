<?php
declare(strict_types=1);
/**
 * Copyright 2019 Luis Alberto Pabón Flores
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace App\Controller;

use App\Generator\Entity\Project;
use App\Generator\Form\ProjectType;
use App\Http\Error;
use App\Http\ErrorResponse;
use JsonException;
use Limenius\Liform\Liform;
use PHPDocker\Generator\Generator;
use function preg_replace;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use const JSON_THROW_ON_ERROR;

/**
 * Contains the project generator endpoints.
 */
class GeneratorController
{
    /**
     * @var Liform
     */
    private $liform;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var Generator
     */
    private $generator;

    public function __construct(Liform $liform, FormFactoryInterface $formFactory, Generator $generator)
    {
        $this->liform      = $liform;
        $this->formFactory = $formFactory;
        $this->generator   = $generator;
    }

    /**
     * This endpoint provides with the generator form schema, as a JSON schema.
     *
     * @return JsonResponse
     */
    public function getGeneratorOptions(): JsonResponse
    {
        $schema = $this->liform->transform($this->formFactory->create(ProjectType::class));

        return new JsonResponse($schema);
    }

    /**
     * This endpoint processes form submission and project generation.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function generate(Request $request): Response
    {
        $project = new Project();
        $form    = $this->formFactory->create(ProjectType::class, $project, ['csrf_protection' => false]);

        try {
            $decoded = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            return new ErrorResponse([new Error('validation-error', 'Not valid json', '')], 400);
        }

        $form->submit($decoded);

        if ($form->isValid() === true) {
            // Generate zip file with docker project
            $zipFile = $this->generator->generate($project);
            $payload = [
                'success'    => true,
                'filename'   => $zipFile->getFilename(),
                'base64Blob' => $zipFile->getBase64EncodedPayload(),
            ];

            $zipFile->delete();

            return new JsonResponse($payload);
        }

        return new ErrorResponse($this->getErrorsFromForm($form), 400);
    }

    /**
     * Given the submitted form, parse out all the errors and return as a list of Error
     *
     * @param FormInterface $form
     *
     * @return Error[]
     */
    private function getErrorsFromForm(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $cause        = $error->getCause();
            $propertyPath = $error->getOrigin()->getName();

            if ($cause instanceof ConstraintViolation) {
                $propertyPath = preg_replace('/^data\./', '', $cause->getPropertyPath());
            }

            $errors[] = new Error('validation-error', $error->getMessage(), $propertyPath);
        }

        return $errors;
    }
}

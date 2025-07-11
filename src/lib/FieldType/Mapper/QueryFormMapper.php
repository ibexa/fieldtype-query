<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final readonly class QueryFormMapper implements FieldDefinitionFormMapperInterface
{
    /**
     * @param array<int|string, string> $queryTypes
     */
    public function __construct(
        private ContentTypeService $contentTypeService,
        private array $queryTypes = []
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $fieldDefinitionForm
     */
    public function mapFieldDefinitionForm(
        FormInterface $fieldDefinitionForm,
        FieldDefinitionData $data
    ): void {
        $parametersForm = $fieldDefinitionForm->getConfig()->getFormFactory()->createBuilder()
            ->create(
                'Parameters',
                Type\TextareaType::class,
                [
                    'label' => 'Parameters',
                    'property_path' => 'fieldSettings[Parameters]',
                ]
            )
            ->addModelTransformer(new ParametersTransformer())
            ->setAutoInitialize(false)
            ->getForm();

        $fieldDefinitionForm
            ->add(
                'QueryType',
                Type\ChoiceType::class,
                [
                    'label' => 'Query type',
                    'property_path' => 'fieldSettings[QueryType]',
                    'choices' => $this->queryTypes,
                    'required' => true,
                ]
            )
            ->add(
                'ReturnedType',
                Type\ChoiceType::class,
                [
                    'label' => 'Returned type',
                    'property_path' => 'fieldSettings[ReturnedType]',
                    'choices' => $this->getContentTypes(),
                    'required' => true,
                ]
            )
            ->add(
                'EnablePagination',
                Type\CheckboxType::class,
                [
                    'label' => 'Enable pagination',
                    'property_path' => 'fieldSettings[EnablePagination]',
                    'required' => false,
                ]
            )
            ->add(
                'ItemsPerPage',
                Type\NumberType::class,
                [
                    'label' => 'Items per page',
                    'property_path' => 'fieldSettings[ItemsPerPage]',
                ]
            )
            ->add($parametersForm);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ezrepoforms_content_type',
            ]);
    }

    /**
     * @return iterable<string, string>
     */
    private function getContentTypes(): iterable
    {
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                yield $contentType->getName() => $contentType->identifier;
            }
        }
    }
}

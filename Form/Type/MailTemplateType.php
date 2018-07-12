<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\MailTemplateEditor\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MailTemplateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tpl_data', TextareaType::class, [
                'label' => trans('plugin_mailtemplateeditor.admin.mail.edit.template_label'),
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_mail_template';
    }
}

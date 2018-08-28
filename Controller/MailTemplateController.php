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

namespace Plugin\MailTemplateEditor\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Util\CacheUtil;
use Eccube\Util\StringUtil;
use Plugin\MailTemplateEditor\Form\Type\MailTemplateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MailTemplateController extends AbstractController
{
    /**
     * @var CacheUtil
     */
    private $CacheUtil;

    /**
     * MailTemplateController constructor.
     *
     * @param CacheUtil $CacheUtil
     */
    public function __construct(CacheUtil $CacheUtil)
    {
        $this->CacheUtil = $CacheUtil;
    }

    /**
     * メールファイル管理一覧画面.
     *
     * @param Request     $request
     *
     * @return array
     *
     * @Route("%eccube_admin_route%/plugin/mailtemplateeditor/mail", name="plugin_MailTemplateEditor_mail")
     * @Template("@MailTemplateEditor/admin/mail.twig")
     */
    public function index(Request $request)
    {
        // Mailディレクトリ(app/template、Resource/template)からメールファイルを取得
        /** @var Finder $finder */
        $finder = Finder::create()->depth(0);
        $mailDir = $this->eccubeConfig['eccube_theme_front_default_dir'].'/Mail';

        $files = [];
        /** @var SplFileInfo $file */
        foreach ($finder->in($mailDir) as $file) {
            $files[$file->getFilename()] = $file->getBasename('.twig');
        }

        $mailDir = $this->eccubeConfig['eccube_theme_front_dir'].'/Mail';
        if (file_exists($mailDir)) {
            foreach ($finder->in($mailDir) as $file) {
                $files[$file->getFilename()] = $file->getBasename('.twig');
            }
        }

        return [
            'files' => $files,
        ];
    }

    /**
     * メール編集画面.
     *
     * @param Request     $request
     * @param $name
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|array
     * @Route("%eccube_admin_route%/plugin/mailtemplateeditor/mail/{name}/edit", name="plugin_MailTemplateEditor_mail_edit")
     * @Template("@MailTemplateEditor/admin/mail_edit.twig")
     */
    public function edit(Request $request, $name)
    {
        $readPaths = [
            // customize folder first
            $this->eccubeConfig['eccube_theme_front_dir'],
            // default folder after that
            $this->eccubeConfig['eccube_theme_front_default_dir'],
        ];

        $fs = new Filesystem();
        $tplData = null;
        $extension = '.twig';
        foreach ($readPaths as $readPath) {
            $filePath = $readPath.'/Mail/'.$name.$extension;
            if ($fs->exists($filePath)) {
                $tplData = file_get_contents($filePath);
                break;
            }
        }

        if (!$tplData) {
            $this->addError('plugin_mailtemplateeditor.admin.mail.edit.error', 'admin');

            return $this->redirectToRoute('plugin_MailTemplateEditor_mail');
        }

        $builder = $this->formFactory->createBuilder(MailTemplateType::class);

        $form = $builder->getForm();

        $form->get('tpl_data')->setData($tplData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ファイル生成・更新
            // save to customize folder
            $filePath = $this->eccubeConfig['eccube_theme_front_dir'].'/Mail/'.$name.$extension;

            $fs = new Filesystem();
            $pageData = $form->get('tpl_data')->getData();
            $pageData = StringUtil::convertLineFeed($pageData);
            $fs->dumpFile($filePath, $pageData);

            // twig キャッシュの削除.
            $this->CacheUtil->clearCache();

            $this->addSuccess('admin.register.complete', 'admin');

            return $this->redirectToRoute('plugin_MailTemplateEditor_mail_edit', [
                'name' => $name,
            ]);
        }

        return [
            'name' => $name,
            'form' => $form->createView(),
        ];
    }

    /**
     * メールファイル初期化処理.
     *
     * @param Request     $request
     * @param $name
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("%eccube_admin_route%/plugin/mailtemplateeditor/mail/{name}/reedit", name="plugin_MailTemplateEditor_mail_reedit")
     */
    public function reedit(Request $request, $name)
    {
        $this->isTokenValid();

        $readPaths = [
            // get old data from default folder
            $this->eccubeConfig['eccube_theme_front_default_dir'],
        ];

        $fs = new Filesystem();
        $tplData = null;
        $extension = '.twig';
        foreach ($readPaths as $readPath) {
            $filePath = $readPath.'/Mail/'.$name.$extension;
            if ($fs->exists($filePath)) {
                $tplData = file_get_contents($filePath);
                break;
            }
        }

        if (!$tplData) {
            $this->addError('plugin_mailtemplateeditor.admin.mail.edit.error', 'admin');

            return $this->redirectToRoute('plugin_MailTemplateEditor_mail');
        }

        $builder = $this->formFactory->createBuilder(MailTemplateType::class);

        $form = $builder->getForm();

        $form->get('tpl_data')->setData($tplData);

        // ファイル生成・更新
        // set to customize folder
        $filePath = $this->eccubeConfig['eccube_theme_front_dir'].'/Mail/'.$name.$extension;

        $fs = new Filesystem();
        $fs->dumpFile($filePath, $tplData);

        $this->addSuccess('plugin_mailtemplateeditor.admin.mail.init.complete', 'admin');

        return $this->redirectToRoute('plugin_MailTemplateEditor_mail_edit', ['name' => $name]);
    }
}

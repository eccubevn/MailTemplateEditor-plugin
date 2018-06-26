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

namespace Plugin\MailTemplateEditor\Tests\Web\Admin;

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;

class MailTemplateControllerTest extends AbstractAdminWebTestCase
{
    public function testRoutingAdminContentMail()
    {
        $client = $this->client;
        $client->request('GET',
            $this->generateUrl('plugin_MailTemplateEditor_mail')
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testRoutingAdminContentMailGet()
    {
        $client = $this->client;

        $client->request('GET',
            $this->generateUrl('plugin_MailTemplateEditor_mail_edit', ['name' => 'order'])
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testRoutingAdminContentMailEdit()
    {
        $client = $this->client;

        $client->request(
            'POST',
            $this->generateUrl('plugin_MailTemplateEditor_mail_edit', ['name' => 'order']),
            [
                'admin_mail_template' => [
                    'tpl_data' => 'testtest',
                    '_token' => 'dummy',
                ],
                'name' => 'order',
            ]
        );

        $this->assertTrue($client->getResponse()->isRedirect($this->generateUrl('plugin_MailTemplateEditor_mail_edit', ['name' => 'order'])));

        $this->expected = 'testtest';
        $this->actual = file_get_contents($this->eccubeConfig['eccube_theme_front_dir'].'/Mail/order.twig');
        $this->verify();
    }

    public function testRoutingAdminContentMailReEdit()
    {
        $client = $this->client;

        $client->request(
            'PUT',
            $this->generateUrl('plugin_MailTemplateEditor_mail_reedit', ['name' => 'order'])
        );

        $this->assertTrue($client->getResponse()->isRedirect());

        $this->expected = file_get_contents($this->eccubeConfig['eccube_theme_front_default_dir'].'/Mail/order.twig');
        $this->actual = file_get_contents($this->eccubeConfig['eccube_theme_front_dir'].'/Mail/order.twig');
        $this->verify();
    }
}

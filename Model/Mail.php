<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magepow\Customform\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;
use Magento\Contact\Model\MailInterface;
use Magento\Contact\Model\ConfigInterface;

class Mail implements MailInterface
{
    /**
     * @var ConfigInterface
     */
    private $contactsConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected $request;

    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $contactsConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        ConfigInterface $contactsConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager = null,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->contactsConfig = $contactsConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->request = $request;
    }

    /**
     * Send email from contact form
     *
     * @param string $replyTo
     * @param array $variables
     * @return void
     */
    public function send($replyTo, array $variables)
    {
        $post = $this->request->getPostValue();
        $sendMe = isset($post['sendmeacopy']) ? true : false;
        $cc = $post['email'];

        /** @see \Magento\Contact\Controller\Index\Post::validatedParams() */
        $replyToName = !empty($variables['data']['name']) ? $variables['data']['name'] : null;

        $this->inlineTranslation->suspend();
        try {
            if($sendMe){
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($this->contactsConfig->emailTemplate())
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getId()
                        ]
                    )
                    ->setTemplateVars($variables)
                    ->setFrom($this->contactsConfig->emailSender())
                    ->addTo($this->contactsConfig->emailRecipient())
                    ->addCc($cc)
                    ->setReplyTo($replyTo, $replyToName)
                    ->getTransport();

            } else {
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($this->contactsConfig->emailTemplate())
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getId()
                        ]
                    )
                    ->setTemplateVars($variables)
                    ->setFrom($this->contactsConfig->emailSender())
                    ->addTo($this->contactsConfig->emailRecipient())
                    ->setReplyTo($replyTo, $replyToName)
                    ->getTransport();

            }

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}

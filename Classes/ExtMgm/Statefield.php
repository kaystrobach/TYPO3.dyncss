<?php

namespace KayStrobach\Dyncss\ExtMgm;

use KayStrobach\Dyncss\Parser\AbstractParser;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Render state field for extension manager configuration
 */
class Statefield
{
    /**
     * @var FlashMessageService
     */
    protected $flashMessageService = null;

    /**
     * Render state field
     */
    public function main()
    {
        $buffer = '';
        $registry = GeneralUtility::makeInstance('KayStrobach\Dyncss\Configuration\BeRegistry');
        $handlers = $registry->getAllFileHandler();
        if (count($handlers)) {
            foreach ($handlers as $extension => $class) {
                /** @var AbstractParser $parser */
                $parser = new $class();
                $buffer .= '<tr><td>*.'.$extension.'</td>';
                $buffer .= '<td>'.$class.'</td>';
                $buffer .= '<td><a href="'.$parser->getParserHomepage().'" target="_blank">'.$parser->getParserName().'</a></td>';
                $buffer .= '<td>'.$parser->getVersion().'</td>';
                $buffer .= '</tr>';
            }
            /** @var FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                'Congrats, you have '.count($handlers).' handlers registered.',
                '',
                FlashMessage::OK,
                true
            );
            $this->addFlashMessage($flashMessage);
            $buffer = '<table class="t3-table table"><thead><tr><th>extension</th><th>class</th><th>name</th><th>version</th></tr></thead>'.$buffer.'</table>';
        } else {
            /** @var FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                'Please install one of the dyncss_* extensions',
                'No handler registered! - No dynamic css is handled at all ;/',
                FlashMessage::WARNING,
                true
            );
            $this->addFlashMessage($flashMessage);
            return $this->renderFlashMessage();
        }
        $renderedFlashMessages = $this->renderFlashMessage();
        return $renderedFlashMessages . $buffer;
    }

    /**
     * Add flash message to message queue
     *
     * @param FlashMessage $flashMessage
     * @return void
     */
    protected function addFlashMessage(FlashMessage $flashMessage)
    {
        if (!($this->flashMessageService instanceof FlashMessageService)) {
            $this->flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        }
        /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
        $defaultFlashMessageQueue = $this->flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * Render queued flash messages
     *
     * @return string
     */
    protected function renderFlashMessage()
    {
        /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
        $defaultFlashMessageQueue = $this->flashMessageService->getMessageQueueByIdentifier();
        return $defaultFlashMessageQueue->renderFlashMessages();
    }
}

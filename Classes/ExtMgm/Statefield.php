<?php

namespace KayStrobach\Dyncss\ExtMgm;

use KayStrobach\Dyncss\Parser\AbstractParser;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use KayStrobach\Dyncss\Configuration\BeRegistry;

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
     *
     * @return string
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function main()
    {
        /** @var BeRegistry $registry */
        $registry = GeneralUtility::makeInstance(BeRegistry::class);
        /** @var array $handlers */
        $handlers = $registry->getAllFileHandler();

        /**
         * @todo in TYPO3 9.5 the file handles can't be detemined, because the extension configuration view now runs in install tool context.
         * We need another solution - this one fixes only the exception in extension configuration view.
         */

        if (count($handlers)) {
            $tableBody = '';
            $tableHead = '<thead><tr><th>extension</th><th>class</th><th>name</th><th>version</th></tr></thead>';
            foreach ($handlers as $extension => $class) {
                /** @var AbstractParser $parser */
                $parser = new $class();
                $tableBody .= '<tr><td>*.' . $extension . '</td>';
                $tableBody .= '<td>' . $class . '</td>';
                $tableBody .= '<td><a href="' . $parser->getParserHomepage() . '" target="_blank">' . $parser->getParserName() . '</a></td>';
                $tableBody .= '<td>' . $parser->getVersion() . '</td>';
                $tableBody .= '</tr>';
            }
            $messageTitle = 'Information';
            $messageBody = 'Congrats, you have ' . count($handlers) . ' handlers registered.';
            $messageHtml = $this->getFlashMessageHtml($messageBody, $messageTitle, FlashMessage::OK);
            $fieldHtml = $messageHtml . '<table class="t3-table table">' . $tableHead . $tableBody . '</table>';
        } else {
            $messageTitle = 'No handler registered! - No dynamic css is handled at all ;/';
            $messageBody = 'Please install one of the dyncss_* extensions';
            $fieldHtml = $this->getFlashMessageHtml($messageBody, $messageTitle, FlashMessage::WARNING);
        }
        return $fieldHtml;
    }

    /**
     * Add flash message to message queue
     *
     * @param FlashMessage $flashMessage
     * @return void
     * @throws \TYPO3\CMS\Core\Exception
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

    /**
     * @param string $messageBody
     * @param string $messageTitle
     * @param int $severity
     * @return string
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function getFlashMessageHtml($messageBody = '', $messageTitle = '', $severity = 1)
    {
        //
        // In TYPO3 9.5 the extension configuration is running in install tool.
        // There is no backend user context available and therefore no flash message queue!
        //
        // Severity:
        $alertType = 'warning';
        if ($severity === 1) {
            $alertType = 'danger';
        } elseif ($severity === 0) {
            $alertType = 'success';
        }
        // Create required HTML
        $html = '<div class="typo3-messages">' . LF;
        $html .= '  <div class="alert alert-' . $alertType . '">' . LF;
        $html .= '    <div class="media">' . LF;
        $html .= '      <div class="media-left">' . LF;
        $html .= '        <span class="fa-stack fa-lg">' . LF;
        $html .= '          <i class="fa fa-circle fa-stack-2x"></i>' . LF;
        if ($alertType === 'danger') {
            $html .= '          <i class="fa fa-times fa-stack-1x"></i>' . LF;
        } elseif ($severity === 'success') {
            $html .= '          <i class="fa fa-check fa-stack-1x"></i>' . LF;
        }
        $html .= '        </span>' . LF;
        $html .= '      </div>' . LF;
        $html .= '      <div class="media-body">' . LF;
        if ($messageTitle !== '') {
            $html .= '        <h4 class="alert-title">' . $messageTitle . '</h4>' . LF;
        }
        if ($messageBody !== '') {
            $html .= '        <p class="alert-message">' . $messageBody . '</p>' . LF;
        }
        $html .= '      </div>' . LF;
        $html .= '    </div>' . LF;
        $html .= '  </div>' . LF;
        $html .= '</div>' . LF;
        return $html;
    }
}

<?php

namespace KayStrobach\Dyncss\ExtMgm;

use KayStrobach\Dyncss\Parser\AbstractParser;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use KayStrobach\Dyncss\Configuration\BeRegistry;

/**
 * Render state field for extension manager configuration
 */
class Statefield
{
    /**
     * Render state field
     *
     * @return string
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function main(): string
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
            $messageHtml = $this->getFlashMessageHtml($messageBody, $messageTitle, ContextualFeedbackSeverity::OK);
            $fieldHtml = $messageHtml . '<table class="t3-table table">' . $tableHead . $tableBody . '</table>';
        } else {
            $messageTitle = 'No handler registered! - No dynamic css is handled at all ;/';
            $messageBody = 'Please install one of the dyncss_* extensions';
            $fieldHtml = $this->getFlashMessageHtml($messageBody, $messageTitle, ContextualFeedbackSeverity::WARNING);
        }
        return $fieldHtml;
    }

    protected function getFlashMessageHtml(string $messageBody, string $messageTitle, ContextualFeedbackSeverity $severity): string
    {
        // the extension configuration runs in the install tool: no flash message queue, plain HTML
        $html = '<div class="alert alert-' . $severity->getCssClass() . '">';
        if ($messageTitle !== '') {
            $html .= '<h4 class="alert-title">' . htmlspecialchars($messageTitle) . '</h4>';
        }
        if ($messageBody !== '') {
            $html .= '<p class="alert-message">' . htmlspecialchars($messageBody) . '</p>';
        }
        return $html . '</div>';
    }
}

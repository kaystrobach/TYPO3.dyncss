<?php
defined('TYPO3_MODE') || die('Access denied.');
call_user_func(
    function ($extKey) {
        // Add/register icons
        if (TYPO3_MODE === 'BE') {
            // register svg icons: identifier and filename
            $iconsSvg = [
                'actions-system-cache-clear-dyncss' => 'actions-system-cache-clear-dyncss.svg'
            ];
            $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
            foreach ($iconsSvg as $identifier => $path) {
                $iconRegistry->registerIcon(
                    $identifier,
                    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                    ['source' => 'EXT:' . $extKey . '/Resources/Public/Icons/' . $path]
                );
            }
        }
    },
    $_EXTKEY
);

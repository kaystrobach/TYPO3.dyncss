<?php

if (version_compare(TYPO3_version, '8.0', '<')) {
    \TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
        [
            'lightning-blue' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY).'Resources/Public/Icons/lightning_blue.png',
        ],
        $_EXTKEY
    );
} else {
    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon("lightning-blue", \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class, [
        'source' => 'EXT:dyncss/Resources/Public/Icons/lightning_blue.png'
    ]);
}
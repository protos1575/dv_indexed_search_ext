<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');
$path2XclassIndexer = t3lib_extMgm::extPath($_EXTKEY).'class.ux_tx_indexer.php';
$TYPO3_CONF_VARS['FE']['XCLASS']['ext/indexed_search/class.indexer.php'] = $path2XclassIndexer;
$TYPO3_CONF_VARS['BE']['XCLASS']['ext/indexed_search/class.indexer.php'] = $path2XclassIndexer;

$path2XclassIndexsearch = t3lib_extMgm::extPath($_EXTKEY).'class.ux_tx_indexedsearch.php';
$TYPO3_CONF_VARS['FE']['XCLASS']['ext/indexed_search/pi/class.tx_indexedsearch.php'] = $path2XclassIndexsearch;
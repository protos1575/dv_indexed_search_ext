The purpose of this extension is to crawl files with indexed_search provided in TYPO3 FE with the dam_frontend extension.


Usage:

- install with ext. manager (tested in TYPO3 Version 4.7 with indexed_search Version 4.7.7)
- create a new page with dam frontend single view
- create a crawler config (=&is=1&tx_damfrontend_pi1[showUid]=[_TABLE:tx_dam;_PID:XXX;_FIELD:uid]  replace XXX with id of your dam storage) restrict config to page dam_frontend single view
- Important: put plugin.tx_damfrontend_pi1 = USER to typoscript template
- crawl page with scheduler task or manually
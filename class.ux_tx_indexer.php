<?php

class ux_tx_indexedsearch_indexer extends tx_indexedsearch_indexer {

    /**
     * Extract links (hrefs) from HTML content and if indexable media is found, it is indexed.
     *
     * @param	string		HTML content
     * @return	void
     */
    function extractLinks($content) {

        // Get links:
        $list = $this->extractHyperLinks($content);

        if ($this->indexerConfig['useCrawlerForExternalFiles'] && t3lib_extMgm::isLoaded('crawler'))	{
            $this->includeCrawlerClass();
            $crawler = t3lib_div::makeInstance('tx_crawler_lib');
        }

        // Traverse links:
        foreach($list as $linkInfo)	{

            // Decode entities:
            if ($linkInfo['localPath'])	{	// localPath means: This file is sent by a download script. While the indexed URL has to point to $linkInfo['href'], the absolute path to the file is specified here!
                $linkSource = t3lib_div::htmlspecialchars_decode($linkInfo['localPath']);
            } else {
                $linkSource = t3lib_div::htmlspecialchars_decode($linkInfo['href']);
            }

            // Parse URL:
            $qParts = parse_url($linkSource);

            // Check for jumpurl (TYPO3 specific thing...)
            if ($qParts['query'] && strstr($qParts['query'],'jumpurl='))	{
                parse_str($qParts['query'],$getP);
                $linkSource = $getP['jumpurl'];
                $qParts = parse_url($linkSource);	// parse again due to new linkSource!
            }

            if (!$linkInfo['localPath'] && $qParts['scheme'])  {
                if ($this->indexerConfig['indexExternalURLs'])	{
                    // Index external URL (http or otherwise)
                    $this->indexExternalUrl($linkSource);
                }

            } elseif (strpos($qParts['query'],'ID=dam_frontend_push') == 1) {
                $this->indexDamDoc($linkInfo['href']);
            } elseif (!$qParts['query']) {
                $linkSource = urldecode($linkSource);
                if (t3lib_div::isAllowedAbsPath($linkSource))	{
                    $localFile = $linkSource;
                } else {
                    $localFile = t3lib_div::getFileAbsFileName(PATH_site.$linkSource);
                }
                if ($localFile && @is_file($localFile))	{

                    // Index local file:
                    if ($linkInfo['localPath'])	{

                        $fI = pathinfo($linkSource);
                        $ext = strtolower($fI['extension']);
                        if (is_object($crawler))	{
                            $params = array(
                                'document' => $linkSource,
                                'alturl' => $linkInfo['href'],
                                'conf' => $this->conf
                            );
                            unset($params['conf']['content']);

                            $crawler->addQueueEntry_callBack(0,$params,'EXT:indexed_search/class.crawler.php:&tx_indexedsearch_files',$this->conf['id']);
                            $this->log_setTSlogMessage('media "'.$params['document'].'" added to "crawler" queue.',1);
                        } else {
                            $this->indexRegularDocument($linkInfo['href'], FALSE, $linkSource, $ext);
                        }
                    } else {
                        if (is_object($crawler))	{
                            $params = array(
                                'document' => $linkSource,
                                'conf' => $this->conf
                            );
                            unset($params['conf']['content']);
                            $crawler->addQueueEntry_callBack(0,$params,'EXT:indexed_search/class.crawler.php:&tx_indexedsearch_files',$this->conf['id']);
                            $this->log_setTSlogMessage('media "'.$params['document'].'" added to "crawler" queue.',1);
                        } else {
                            $this->indexRegularDocument($linkSource);
                        }
                    }
                }
            }
        }
    }


    /**
     * Index DAM Frontend Docs
     *
     * @param	string		URL, eg. "http://typo3.org/"
     * @return	void
     * @see indexRegularDocument()
     */
    function indexDamDoc($externalUrl)	{

        // Parse External URL:
        $qParts = parse_url($externalUrl);
        $fI = pathinfo($qParts['path']);
        $ext = strtolower($fI['extension']);

        // Get headers:
        $urlHeaders = $this->getUrlHeaders($GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'].$externalUrl);
        if (stristr($urlHeaders['Content-Type'],'application/force-download'))	{
            $content = $this->indexExternalUrl_content = t3lib_div::getUrl($GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'].$externalUrl);
            if (strlen($content))	{

                // Create temporary file:
                $filename = explode('filename=',$urlHeaders['Content-disposition']);
                preg_match('/\"(.*)\"/', $filename[1], $status);
                $ext = pathinfo($status[1], PATHINFO_EXTENSION);
                $tmpFile = t3lib_div::tempnam($status[1]);
                if ($tmpFile) {
                    t3lib_div::writeFile($tmpFile, $content);

                    // Index that file:
                    $this->indexRegularDocument($externalUrl, TRUE, $tmpFile, $ext);	// Using "TRUE" for second parameter to force indexing of external URLs (mtime doesn't make sense, does it?)
                    unlink($tmpFile);
                }
            }
        }
    }


}
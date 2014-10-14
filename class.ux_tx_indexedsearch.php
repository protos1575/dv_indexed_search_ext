<?php

class ux_tx_indexedsearch extends tx_indexedsearch {
    /**
     * Check if the record is still available or if it has been deleted meanwhile.
     * Currently this works for files only, since extending it to page content would cause a lot of overhead.
     *
     * @param	array		Result row array
     * @return	boolean		Returns TRUE if record is still available
     */
    function checkExistance($row) {
        $recordExists = TRUE;	// Always expect that page content exists

        if ($row['item_type']) {        // External media:
            if (!is_file($row['data_filename']) || !file_exists($row['data_filename'])) {
                $recordExists = FALSE;
            }

            if (strpos($row['data_filename'],'ID=dam_frontend_push') >= 1) {
                $urlHeaders = $this->getUrlHeaders($GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'].$row['data_filename']);
                if (stristr($urlHeaders['Content-Type'],'application/force-download'))	{
                    $recordExists = TRUE;
                }
            }
        }

        return $recordExists;
    }

    /**
     * Getting HTTP request headers of URL
     *
     * @param	string		The URL
     * @param	integer		Timeout (seconds?)
     * @return	mixed		If no answer, returns FALSE. Otherwise an array where HTTP headers are keys
     */
    function getUrlHeaders($url)	{
        $content = t3lib_div::getUrl($url,2);	// Try to get the headers only

        if (strlen($content))	{
            // Compile headers:
            $headers = t3lib_div::trimExplode(LF,$content,1);
            $retVal = array();
            foreach($headers as $line)	{
                if (!strlen(trim($line)))	{
                    break;	// Stop at the first empty line (= end of header)
                }

                list($headKey, $headValue) = explode(':', $line, 2);
                $retVal[$headKey] = $headValue;
            }
            return $retVal;
        }
    }

}
<?php

namespace NewsML_G2\Plugin\Parser\NewsML\Vendor;

use NewsML_G2\Plugin\Parser\NewsML\NewsMLParser;
use NewsML_G2\Plugin\Parser\NewsML\NewsMLObject;

/**
 * Provides the capability to parse a XML file containing NewsML-G2 data from Innodata.
 *
 * @author Alexander Kucherov
 * @since 1.1.0
 */
class NewsMLParserInnodata extends NewsMLParser
{
    /**
     * The unique identifier which is used by can_parse to determine if the file to parse is from Innodata.
     *
     * @var string $_provider
     * @access private
     */
    private $_provider = 'innodata.com';

    /**
     * Supported standard identifier.
     *
     * @var string $standard
     * @access protected
     */
    protected $standard = 'NewsML-G2';

    /**
     * {@inheritdoc}
     */
    public function can_parse($file)
    {
        $xpath = $this->generate_xpath_on_xml($file);
        $query = '//tempNS:newsItem';
        $result = $xpath->query($query);

        if ($item = $result->item(0)) {
            if ($item->getAttribute('literal') == $this->_provider) {
                // Expected, but not provided. So skip this check
            }
            if ($item->getAttribute('standard') == $this->standard) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($doc)
    {
        // Create a new NewsML_Object that stores all our data and will be added later to an array
        $news_object = new NewsMLObject();

        $guid = $copyrightholder = $copyrightnotice = $timestamp = $content = '';
        $titles = array_fill_keys(array('title', 'subtitle'), '');
        $mediatopics = $locations = array();

        // No itemSets to loop through
        $guid = $this->get_guid_from_newsml($doc);
        $copyrightholder = $this->get_copyrightholder_from_newsml($doc);
        $timestamp = $this->get_datetime_from_newsml($doc);
        $titles = $this->get_titles_from_newsml($doc);
        $mediatopics = $this->get_mediatopics_from_newsml($doc);
        $publish_date = $this->get_publish_date_from_newsml($doc);
        $source_uri = $this->get_source_uri_from_newsml($doc);
        $source = $this->get_source_from_newsml($doc);
        $version = $this->get_version_from_newsml($doc);
        $content = $this->get_content_from_newsml($doc);

        $news_object->set_guid($guid);
        $news_object->set_timestamp($timestamp);
        $news_object->set_copyrightholder($copyrightholder);
        //$news_object->set_copyrightnotice( $copyrightnotice );
        $news_object->set_title($titles['title']);
        $news_object->set_subtitle($titles['subtitle']);
        $news_object->set_mediatopics($mediatopics);
        //$news_object->set_locations( $locations );
        $news_object->set_publish_date($publish_date);
        $news_object->set_source_uri($source_uri);
        $news_object->set_source($source);
        $news_object->set_version($version);
        $news_object->set_content($content);

        return $news_object;
    }

    /**
     * {@inheritdoc}
     */
    public function get_guid_from_newsml($xml)
    {
        if ($guid = $this->get_meta_id_from_newsml($xml)) {
            return $guid;
        }

        return sha1(json_encode(@simplexml_load_string($xml)));
    }

    /**
     * Gets the meta id from the XML and returns it as XML string.
     * @param \DOMDocument $xml The DOM Tree of the file to parse.
     *
     * @return string The meta id if found, otherwise an empty string.
     * @since 1.2.11
     * @author Alexander Kucherov
     */
    public function get_meta_id_from_newsml($xml)
    {
        $xpath = $this->generate_xpath_on_xml($xml);

        $query_itemmeta = '//tempNS:itemMeta';
        $result_itemmeta = $xpath->query($query_itemmeta);

        if ($item = $result_itemmeta->item(0)) {
            if ($itemmeta= $item->getAttribute('id')) {
                return $itemmeta;
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function get_titles_from_newsml($xml)
    {
        $xpath = $this->generate_xpath_on_xml($xml);

        $query_title = '//tempNS:headline';
        $result_title = $xpath->query($query_title);

        $title = "";
        if ($result_title->length > 0) {
            $title = $result_title->item(0)->nodeValue;
        }

        $subtitle = "";
        if ($result_title->length > 1) {
            $subtitle = $result_title->item(1)->nodeValue;
        }

        $all_titles = array(
            'title' => $title,
            'subtitle' => $subtitle,
        );

        return $all_titles;
    }

    /**
     * {@inheritdoc}
     */
    public function get_copyrightholder_from_newsml($xml)
    {
        return $this->_provider;
    }

    /**
     * {@inheritdoc}
     */
    public function get_mediatopics_from_newsml($xml)
    {
        $xpath = $this->generate_xpath_on_xml($xml);

        // Get all media topics
        $query_mediatopics = '//tempNS:title';
        $result_mediatopics = $xpath->query($query_mediatopics);

        $topics = array();

        // Only 1 provided by innodata
        if ($result_mediatopics->length > 0) {
            $topics[] = $result_mediatopics->item(0)->nodeValue;
        }

        return $topics;
    }

    /**
     * {@inheritdoc}
     */
    public function get_content_from_newsml($xml)
    {
        $xpath = $this->generate_xpath_on_xml($xml);

        // <description>random text with <inline uri="http://url.com/">inline</inline> XML to parse</description>
        $query_description = '//tempNS:description/tempNS:inline';
        $inlines = $xpath->query($query_description);

        // Replace inlines.
        if ($inlines->item(0)) {
            foreach ($inlines as $inline) {
                $this->cloneNode($inline, 'a', ['uri' => 'href']);
            }
            $xml->saveXML();
        }

        // Get all descriptions.
        $query_description = '//tempNS:description';
        $result_description = $xpath->query($query_description);

        if ($item = $result_description->item(0)) {
            if ($description = strip_tags($item->C14N() ?: '', '<a>')) {
                return nl2br($description);
            }
        }

        return '';
    }

    /**
     * Gets the publication time from the XML and returns it as XML DateTime.
     *
     * @param \DOMDocument $xml The DOM Tree of the file to parse.
     *
     * @return string The creation time if found, otherwise an empty string.
     * @author Alexander Kucherov
     */
    public function get_publish_date_from_newsml($xml)
    {
        $xpath = $this->generate_xpath_on_xml($xml);

        $query_datetime = '//tempNS:dateline';
        $result_datetime = $xpath->query($query_datetime);

        // Convert from XML datetime to a unix timestamp.
        if ($item = $result_datetime->item(0)) {
            if ($timestamp = strtotime($item->nodeValue)) {
                return $timestamp;
            }
        }

        return '';
    }

    /**
     * Gets the source uri from the XML and returns it as XML string.
     * @param \DOMDocument $xml The DOM Tree of the file to parse.
     *
     * @return string The source uri if found, otherwise an empty string.
     * @author Alexander Kucherov
     * @deprecated Deprecated since version 1.2.2. For now used as a fallback function.
     */
    public function _get_source_uri_from_newsml($xml)
    {
        $xpath = $this->generate_xpath_on_xml($xml);

        // <description>random text with <inline uri="http://url.com/">inline</inline> XML to parse</description>
        $query_description = '//tempNS:description/tempNS:inline';
        $inlines = $xpath->query($query_description);

        if ($item = $inlines->item(0)) {
            if ($infosource = $item->getAttribute('uri')) {
                return $infosource;
            }
        }

        $query_infosource = '//tempNS:infoSource';
        $result_infosource = $xpath->query($query_infosource);

        if ($item = $result_infosource->item(0)) {
            if ($infosource = $item->getAttribute('uri')) {
                return $infosource;
            }
        }

        return '';
    }

    /**
     * Gets the source uri from the XML and returns it as XML string.
     * @param \DOMDocument $xml The DOM Tree of the file to parse.
     *
     * @return string The source uri if found, otherwise an empty string.
     * @author Alexander Kucherov
     */
    public function get_source_uri_from_newsml($xml)
    {
        if ($infosource = $this->_get_source_uri_from_newsml($xml)) {
            return $infosource;
        }

        $xpath = $this->generate_xpath_on_xml($xml);

        $query_infosource = '//tempNS:description';
        $result_infosource = $xpath->query($query_infosource);

        if ($item = $result_infosource->item(0)) {
            if ($infosource = $item->getAttribute('creatoruri')) {
                return $infosource;
            }
        }

        return '';
    }

    /**
     * Gets the source from the XML and returns it as XML string.
     * @param \DOMDocument $xml The DOM Tree of the file to parse.
     *
     * @return string The source if found, otherwise an empty string.
     * @author Alexander Kucherov
     * @since 1.2.2
     * @deprecated Deprecated since version 1.2.5. For now used as a fallback function
     */
    public function get_source_from_newsml($xml)
    {
        $xpath = $this->generate_xpath_on_xml($xml);

        if ($infosource = $this->_get_source_from_newsml($xml)) {
            return $infosource;
        }

        // Source name.
        $query_name = '//tempNS:name';
        $result_name = $xpath->query($query_name);

        if ($item = $result_name->item(0)) {
            if ($name = $item->nodeValue) {
                return $name;
            }
        }

        // Fallback function, if no source name was provided.
        if ($url = $this->get_source_uri_from_newsml($xml)) {
            return parse_url($url, PHP_URL_HOST) ?? '';
        }

        return '';
    }

    /**
     * Gets the source from the XML and returns it as XML string.
     * @param \DOMDocument $xml The DOM Tree of the file to parse.
     *
     * @return string The source if found, otherwise an empty string.
     * @author Alexander Kucherov
     * @since 1.2.5
     */
    public function _get_source_from_newsml($xml)
    {
        $xpath = $this->generate_xpath_on_xml($xml);

        // Source name.
        $query_name = '//tempNS:infoSource/tempNS:name';
        $result_name = $xpath->query($query_name);

        if ($item = $result_name->item(0)) {
            if ($name = $item->nodeValue) {
                return $name;
            }
        }

        return '';
    }
}

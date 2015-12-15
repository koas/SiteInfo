<?php

/**
 * SiteInfo class. Retrieves some information about a web page.
 * @version 1.0
 * @author Koas <alvaro.calleja@gmail.com>
 * @see https://github.com/koas/SiteInfo
 */

class SiteInfo
{
	/**
	 * URL provided by the user when creating the class instance
	 * @var string
	 */
	private $url;

	/**
	 * XML object created from the site HTML
	 * @var DOMDocument
	 */
	private $xmlDoc;

	/**
	 * Class constructor
	 * @param string $url URL of the site 
	 */
	function __construct($url)
	{
		$this->url = $url;

		$this->getXML();
	}

	/**
	 * Returns the title of the site
	 * @return string Title of the site or empty string if not set
	 */
	public function getTitle()
	{
		// Try first the title tag
		$title = $this->xmlDoc->getElementsByTagName('title')->item(0)->nodeValue;

		// If not set, try the OpenGraph title
		if ($title == '')
		{
			$title = $this->getTagValue('meta', 'property', 'og:title',
										'content');
		}

		// If not set, try the Twitter Cards title
		if ($title == '')
		{
			$title = $this->getTagValue('meta', 'property', 'twitter:title',
										'content');
		}

		return $title;
	}

	/**
	 * Returns the description of the site
	 * @return string Description of the site or empty string if not set
	 */
	public function getDescription()
	{
		// Try first the meta name="description" tag
		$desc = $this->getTagValue('meta', 'name', 'description', 'content');

		// If not set, try the OpenGraph description
		if ($desc == '')
		{
			$desc = $this->getTagValue('meta', 'property', 'og:description',
									   'content');
		}

		// If not set, try the Twitter Cards description
		if ($desc == '')
		{
			$desc = $this->getTagValue('meta', 'property',
									   'twitter:description', 'content');
		}

		return $desc;
	}

	/**
	 * Returns the keywords of the site
	 * @return string Keywords of the site, or empty string if not set
	 */
	public function getKeywords()
	{
		return $this->getTagValue('meta', 'name', 'keywords', 'content');
	}

	/**
	 * Returns the favicon of the site
	 * @return string Favicon file URL, or empty if not set
	 */
	public function getIcon()
	{
		// Try first the link rel="shortcut icon" tag
		$icon = $this->getTagValue('link', 'rel', 'shortcut icon', 'href');

		// If not set, try the link rel="icon" tag
		if ($icon == '')
			$icon = $this->getTagValue('link', 'rel', 'icon', 'href');			

		return $this->relativeURLtoAbsolute($icon);
	}

	/**
	 * Returns an image for the site
	 * @return string Image file URL, or empty if not set
	 */
	public function getImage()
	{
		// We'll try some options, higher resolutions first
		// OpenGraph image
		$img = $this->getTagValue('meta', 'property', 'og:image', 'content');

		// If empty, try MS application tile image
		if ($img == '')
		{
			$img = $this->getTagValue('meta', 'name', 'msapplication-TileImage',
									  'content');
		}

		// If empty, try Fluid icon
		if ($img == '')
		{
			$img = $this->getTagValue('link', 'rel', 'fluid-icon', 'href');
		}

		// If empty, try Twitter Cards image
		if ($img == '')
			$img = $this->getTagValue('meta', 'name', 'twitter:image', 'content');

		// If empty, try Apple web application icons
		if ($img == '')
			$img = $this->getTagValue('link', 'sizes', '152x152', 'href');
		if ($img == '')
			$img = $this->getTagValue('link', 'sizes', '144x144', 'href');
		if ($img == '')
			$img = $this->getTagValue('link', 'sizes', '120x120', 'href');
		if ($img == '')
			$img = $this->getTagValue('link', 'sizes', '114x114', 'href');
		if ($img == '')
			$img = $this->getTagValue('link', 'sizes', '76x76', 'href');
		if ($img == '')
			$img = $this->getTagValue('link', 'sizes', '72x72', 'href');
		if ($img == '')
			$img = $this->getTagValue('link', 'sizes', '57x57', 'href');

		// If everything fails, use favicon image
		if ($img == '')
			$img = $this->getIcon();

		return $this->relativeURLtoAbsolute($img);	
	}

	/**
	 * Returns an attribute from a tag that matches an attribute-value pair
	 * @param  string $tag        Tag name
	 * @param  string $attr       Attribute name to match
	 * @param  string $attrValue  Attribute value to match
	 * @param  string $attrSearch Desired attribute
	 * @return string             Value of the desired attribute, or an empty
	 *                            string if not found
	 */
	public function getTagValue($tag, $attrName, $attrValue, $attrSearch)
	{
		$nodes = $this->xmlDoc->getElementsByTagName($tag);
		for ($x = 0; $x < $nodes->length; ++$x)
		{
			$node = $nodes->item($x);
			if ($node->getAttribute($attrName) == $attrValue)
				return $node->getAttribute($attrSearch);
		}

		return '';
	}

	/**
	 * Loads the site HTML and creates an XML object
	 */
	private function getXML()
	{
		// Get HTML
		$ch = curl_init();

	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $this->url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	    $html = curl_exec($ch);
	    curl_close($ch);

	    // Create XML from HTML
	    $this->xmlDoc = new DOMDocument();
		@$this->xmlDoc->loadHTML($html);
	}

	/**
	 * Checks if an URL is relative, and turns it into absolute
	 * @param  string $url URL to check
	 * @return string      Absolute URL
	 */
	private function relativeURLtoAbsolute($url)
	{
		// If empty string return
		if ($url == '')
			return '';

		// Check if URL is relative
		if (substr($url, 0, 4) != 'http')
		{
			$p = parse_url($this->url);
			
			$prefix = $p['scheme'].'://';
			if (isset($p['user']))
				$prefix .= $p['user'].':';
			if (isset($p['pass']))
				$prefix .= $p['pass'];
			if (isset($p['user']) || isset($p['pass']))
				$prefix .= '@';
			$prefix .= $p['host'];
			if (isset($p['port']))
				$prefix .= ':'.$p['port'];
			if ($url{0} != '/')
				$prefix .= '/';

			$url = $prefix.$url;
		}

		return $url;
	}
}

<?php

/**
 * Class pagination
 */

class pagination  {

/**
 * CLASS PROPERTIES AND METHODS
 */
	private $offset;
	private $limit;
	private $total;
	private $pages;
	
	public function __construct($offset, $limit, $total) {
		$this->offset = (int)$offset;
		$this->limit = (int)$limit;
		$this->total = (int)$total;
		
		$this->offset = $this->offset == 0 ? 1 : $this->offset; 
		$this->limit = $this->limit == 0 ? 1 : $this->limit; 
		$this->total = $this->total == 0 ? 1 : $this->total; 
	}
	
	public function do_pagination() {
		$this->pages = ceil($this->total / $this->limit);
		if ($this->offset >= $this->pages) {
			$this->offset = $this->pages;
		}
	}
	
/**
 * STATIC PROPERTIES AND METHODS
 */
	private static $_instance;
	
	public static function init($offset, $limit, $total) {
		if (self::$_instance == null) {
			self::$_instance = new pagination($offset, $limit, $total);
		}
	}
	
	public static function get_vars() {
		return array(
			'offset'	=> self::$_instance->offset,
			'limit'		=> self::$_instance->limit,
			'total'		=> self::$_instance->total,
			'pages'		=> self::$_instance->pages
		);
	}

	public static function paginate($alink, $sep = "&nbsp;") {
		self::$_instance->do_pagination();
		$link = "<a href=\"%s\">%s</a>%s";
		$pages = "";
		for($i = 1; $i <= self::$_instance->pages; $i++) {
			$pages .=  sprintf(
				$link,
				url::site_url($alink . "/" . $i),
				(self::$_instance->offset == $i) ? "<strong>$i</strong>" : $i,
				$sep
			);
		}
		$first = self::$_instance->offset == 1 ? "" : sprintf(
			$link,
			url::site_url($alink . "/" . "1"),
			"First",
			$sep
		);
		$last = self::$_instance->offset == self::$_instance->pages ? "" : sprintf(
			$link,
			url::site_url($alink . "/" . self::$_instance->pages),
			"Last",
			$sep
		);
		$next = self::$_instance->offset == self::$_instance->pages ? "" : sprintf(
			$link,
			url::site_url($alink . "/" . (self::$_instance->offset + 1)),
			"Next",
			$sep
		);
		$prev = self::$_instance->offset == 1 ? "" : sprintf(
			$link,
			url::site_url($alink . "/" . (self::$_instance->offset - 1)),
			"Previous",
			$sep
		);
		$links = substr(sprintf(
			"%s%s%s%s%s",
			$first,
			$prev,
			$pages,
			$next,
			$last
		), 0, -strlen($sep));
		return $links;
	}

}

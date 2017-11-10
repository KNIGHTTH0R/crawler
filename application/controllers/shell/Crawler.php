<?php
/**
 * Created by IntelliJ IDEA.
 * User: well
 * Date: 11/8/17
 * Time: 1:07 PM
 */

class Crawler extends CI_Controller {

	private $crawlers;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('content_model');
		$this->crawlers = [
			new TribunCrawler($this->content_model),
			new AntaraCrawler($this->content_model),
			new TempoCrawler($this->content_model),
			new KompasCrawler($this->content_model),
			new VivaCrawler($this->content_model),
			new DetikCrawler($this->content_model)];
	}


	public function crawl() {
		while (true) {
			foreach ($this->crawlers as $crawler) {
				$crawler->crawl();
				sleep(3);
			}
		}
	}

}

abstract class BaseCrawler {
	protected $url;
	private $processedUrl = [];
	protected $blockedUrl = [];

	/**
	 * selector untuk url-url yang akan di-crawl
	 */
	protected $firstUrlSelector;

	protected $titleSelector;
	protected $contentSelector;
	protected $imgSelector;

	abstract protected function saveToDB($title, $content, $img);

	public function crawl() {
		print "blocked url: " . implode(", ", $this->blockedUrl) . "\n";
		foreach ($this->url as $url) {
			print 'crawling url: ' . $url . "\n";
			$contentDom = Sunra\PhpSimple\HtmlDomParser::file_get_html($url);

			if ($contentDom) {
				$nodeElms = $contentDom->find($this->firstUrlSelector);

				$urls = array_map(function ($x) {
					print "Got url: {$x->href}\n";
					return $x->href;
				}, $nodeElms);

				$urls = array_unique($urls);

				print "done crawl for url: $url\n";

				$urls = array_filter($urls, function ($_url) {
					$isProcessed = in_array($_url, $this->processedUrl);
					$isBlocked = in_array(parse_url($_url, PHP_URL_HOST), $this->blockedUrl);

					if ($isProcessed || $isBlocked)
						print "url has been crawled before or blocked, ignoring url : $_url\n";
					else
						$this->processedUrl[] = $_url;

					return !($isProcessed || $isBlocked);
				});

				foreach ($urls as $_url) {
					print 'crawling url: ' . $_url . "\n";
					$_dom = Sunra\PhpSimple\HtmlDomParser::file_get_html($_url);

					if ($_dom) {
						$title = '';
						$content = '';
						$titleDom = $_dom->find($this->titleSelector, 0);
						$contentDom = $_dom->find($this->contentSelector, 0);

						if ($titleDom)
							$title = $titleDom->innertext();

						if ($contentDom)
							$content = $contentDom->innertext();

						if (empty($title) || empty($content)) {
							print "cannot get title or content, continue to next url...\n";
							continue;

						}

						$config = HTMLPurifier_Config::createDefault();
						$purifier = new HTMLPurifier($config);
						$content = $purifier->purify($content);
						$img = $_dom->find($this->imgSelector[0], 0)->getAttribute($this->imgSelector[1]);;
						print "done crawl for url: $_url\n";
						$this->saveToDB($title, $content, $img);
					} else {
						print "cannot fetch url: $_url \n";
					}

					sleep(3);

				};

				print "===========================================\n";
			} else {
				print "cannot fetch url: $url \n";
			}
			sleep(3);

		}
	}
}

class VivaCrawler extends BaseCrawler {
	private $model;
	private $baseURL = 'http://www.viva.co.id';
	private $subURLS = ['bola', 'berita', 'sport', 'digital', 'otomotif', 'showbiz', 'gaya-hidup'];

	protected $url;

	protected $firstUrlSelector = '#load_segment .title-content';
	protected $titleSelector = '.leading-title h1';
	protected $contentSelector = '.article-detail #article-detail-content';
	protected $imgSelector = ["meta[property='og:image']", "content"];

	protected function saveToDB($title, $content, $img) {
		$this->model->save($title, strip_tags($content), $img);
	}

	public function __construct($model) {
		$this->model = $model;
		$this->url = array_map(function ($suburl) {
			return $this->baseURL."/".$suburl;
		}, $this->subURLS);
	}

}

class DetikCrawler extends BaseCrawler {
	private $model;

	protected $url = ['https://www.detik.com/'];
	protected $blockedUrl = ['health.detik.com'];

	protected $firstUrlSelector = '.desc_nhl > a';
	protected $titleSelector = '.jdl > h1';
	protected $contentSelector = '#detikdetailtext';
	protected $imgSelector = ["meta[property='og:image']", "content"];

	protected function saveToDB($title, $content, $img) {
		$this->model->save($title, strip_tags($content), $img);
	}

	public function __construct($model) {
		$this->model = $model;
	}
}

class KompasCrawler extends BaseCrawler {
	private $model;

	protected $url = ['http://indeks.kompas.com//'];

	protected $firstUrlSelector = '.article__link';
	protected $titleSelector = '.read__title';
	protected $contentSelector = '.read__content';
	protected $imgSelector = ["meta[property='og:image']", "content"];

	protected function saveToDB($title, $content, $img) {
		$this->model->save($title, strip_tags($content), $img);
	}

	public function __construct($model) {
		$this->model = $model;
	}
}

class TempoCrawler extends BaseCrawler {
	private $model;

	protected $url = ['https://www.tempo.co/indeks'];

	protected $firstUrlSelector = '.col .list .card > .wrapper > a[href]';
	protected $titleSelector = '#article article > h1';
	protected $contentSelector = '#isi';
	protected $imgSelector = ["meta[property='og:image']", "content"];

	protected function saveToDB($title, $content, $img) {
		$this->model->save($title, strip_tags($content), $img);
	}

	public function __construct($model) {
		$this->model = $model;
	}
}

class AntaraCrawler extends BaseCrawler {
	private $model;

	protected $url = ['http://www.antaranews.com/'];

	protected $firstUrlSelector = '.news-feed .widget-content article > h3 > a';
	protected $titleSelector = '.post-title';
	protected $contentSelector = '.post-content';
	protected $imgSelector = [".image-overlay", "src"];

	protected function saveToDB($title, $content, $img) {
		$this->model->save($title, strip_tags($content), $img);
	}

	public function __construct($model) {
		$this->model = $model;
	}
}

class TribunCrawler extends BaseCrawler {
	private $model;

	protected $url = ['http://www.tribunnews.com'];

	protected $firstUrlSelector = '#latestul .art-list h3 > a';
	protected $titleSelector = '#article > h1';
	protected $contentSelector = '#article .txt-article';
	protected $imgSelector = ["meta[property='og:image']", "content"];

	protected function saveToDB($title, $content, $img) {
		$this->model->save($title, strip_tags($content), $img);
	}

	public function __construct($model) {
		$this->model = $model;
	}
}

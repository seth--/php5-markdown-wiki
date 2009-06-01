<?php

class MarkdownWiki {
	protected $config;
	protected $parser;

	public function __construct($config=false) {
		$this->initWiki();
		if ($config) {
			$this->setConfig($config);
		}
	}
	
	protected function initWiki() {
		$baseDir = dirname(__FILE__) . '/';

		// Including the markdown parser
		//echo "BaseDir: {$baseDir}\n";
		require_once $baseDir . 'markdown.php';
	}
	
	public function setConfig($config) {
		$this->config = $config;
	}

	public function handleRequest($request=false, $server=false) {
		$action = $this->parseRequest($request, $server);
		
	}
	
	
	public function parseRequest($request=false, $server=false) {
		$action = (object) NULL;

		if (!$request) { $request = $_REQUEST; }
		if (!$server)  { $server  = $_SERVER;  }
		
		//echo "Request: "; print_r($request);
		//echo "Server : "; print_r($server);
		
		$action->method = $this->getMethod($request, $server);
		$action->page   = $this->getPage($request, $server);
		$action->action = $this->getAction($request, $server);

		if ($action->method=='POST') {
			$action->post = $this->getPostDetails($request, $server);
		}		

		return $action;
	}
	
	protected function getMethod($request, $server) {
		if (!empty($server['REQUEST_METHOD'])) {
			return $server['REQUEST_METHOD'];
		}
		return 'UNKNOWN';
	}
	
	protected function getPage($request, $server) {
		$page = '';
		
		// Determine the page name
		if (!empty($server['PATH_INFO'])) {
			//echo "Path info detected\n";
			// If we are using PATH_INFO then that's the page name
			$page = substr($server['PATH_INFO'], 1);
			
		} elseif (!empty($request['id'])) {
			$page = $request['id'];
			
		} else {
			// TODO: Keep checking
			echo "WARN: Could not find a pagename\n";
		}

		// Check whether a default Page is being requested
		if ($page=='' || preg_match('/\/$/', $page)) {
			$page .= $this->config['defaultPage'];
		}
		
		return $page;
	}
	
	protected function getAction($request, $server) {
		if ($server['REQUEST_METHOD']=='POST') {
			if (!empty($request['preview'])) {
				return 'preview';
			} elseif (!empty($request['save'])) {
				return 'save';
			}
		} elseif (!empty($request['action'])) {
			return $request['action'];
		} elseif (!empty($server['PATH_INFO'])) {
			return 'display';
		}
		return 'UNKNOWN';
	}
	
	protected function getPostDetails($request, $server) {
		$post = (object) NULL;
		$post->text    = $request['text'];
		$post->updated = $request['updated'];
		return $post;
	}

}



if ($_REQUEST) {
	# Dealing with a web request
	$wiki = new MarkdownWiki($config);
	$wiki->handleRequest();
	//print_r($wiki);
}

?>
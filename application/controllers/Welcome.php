<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Welcome extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('content_model');
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$data = $this->content_model->getData()->result();
		$this->load->view('welcome_message', ['data' => $data]);
	}
}

class Content {
	public $title;
	public $content;
	public $img;

	public function __construct($title, $content, $img)
	{
		$this->title = $title;
		$this->content = $content;
		$this->img = $img;
	}
}

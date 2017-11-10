<?php
/**
 * Created by IntelliJ IDEA.
 * User: well
 * Date: 11/8/17
 * Time: 2:59 PM
 */

class Content_model extends CI_Model {

	private $usePusher;

	public function __construct()
	{
		$this->load->database();
		$this->usePusher = getenv('USE_PUSHER');
	}

	public function save($title, $content, $img) {

		$isExisting = count($this->db->get_where('content', array('title' => $title))->result()) > 0;

		if (!$isExisting) {
			print "save to db: $title\n";
			$data = ['title' => $title, 'description' => $content, 'img' => $img];

			if ($this->db->insert('content', $data)) {
				if($this->usePusher == "true") {
					$pusher = new Pusher\Pusher(getenv('PUSHER_KEY'), getenv('PUSHER_SECRET'), getenv('PUSHER_APPID'), array('cluster' => getenv('PUSHER_CLUSTER')));
					$data['description'] = substr($content, 0, 70);

					$pusher->trigger('my-channel', 'my-event', array('message' => $data));
				}
			}
		} else {
			print "data is exists. ignoring...\n";
		}
	}

	public function getData($limit = 10, $sinceId = 0) {
		if ($sinceId > 0)
			$this->db->where('id <', $sinceId);

		$this->db->order_by('id', 'DESC');
		$this->db->limit(10);
		return $this->db->get('content');
	}
}

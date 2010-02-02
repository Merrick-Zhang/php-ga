<?php
class Foo extends Controller {

    function Foo() {
        parent::Controller();

        $this->load->library('ga', array('account' => 'MO-XXXXXX-XX'));
    }

    function index() {
        $data = array(
            'ga' => $this->ga->url(base_url() . 'foo/ga');
        );

        $this->load->view('foo.php', $data);
    }

    function ga() {
        $this->ga->track();
    }

}
?>
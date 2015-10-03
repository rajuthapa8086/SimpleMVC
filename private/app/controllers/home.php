<?php

class home {

    public function index() {
        action::load_template('', 'home', array(
            'msg' => 'Hello World',
        ));
    }

}
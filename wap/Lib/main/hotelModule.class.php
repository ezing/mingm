<?php

class hotelModule extends MainBaseModule
{

	public function index()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->display("hotel.html");
	}
}
<?php

interface InterfaceCaptcha {
	public function __construct ($salt);
	public function getToken ();
	public function getHtml ();
	public function echoHtml ();
	public function isValidToken ($responseToken);
}
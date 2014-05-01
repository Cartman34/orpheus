<?php

class ForumPost extends PermanentEntity {

	//Attributes
	protected static $table		= 'forum_post';
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;

	public function __toString() {
		return $this->name;
	}

	public function getLink() {
		return u('forum_post', $this->id());
	}
}
ForumPost::init();

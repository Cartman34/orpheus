<?php
/* @var $USER SiteUser */

$ALLOW_EDITOR	= SiteUser::loggedCanDo('forum_manage');

HTMLRendering::addJSFile('debug.js');
HTMLRendering::addJSFile('bootstrap-wysiwyg.js');
HTMLRendering::addJSFile('forum-forums.js');
if( $ALLOW_EDITOR ) {
	HTMLRendering::addJSFile('forum-editor.js');
}

$TOPBAR_CONTENTS	= <<<EOF
<form class="navbar-form navbar-right">
	<button type="button" class="editmode-btn btn btn-default">Edit Mode <span class="icon-edit"></span></button>
	<button type="button" class="login-btn btn btn-default" data-toggle="modal" data-target="#connectForm">Log in<span class="icon-off"></span></button>
	<input type="text" placeholder="What are you lookin' for ?" autofocus="autofocus" class="form-control search-query">
	<button type="submit" class="btn btn-default" name="submitSearch">Search</button>
</form>
EOF;

$AllForums	= Forum::getAll();
$Forums		= array();
foreach( $AllForums as $forum ) {
	if( !isset($Forums[$forum->parent_id]) ) {
		$Forums[$forum->parent_id]	= array();
	}
	$Forums[$forum->parent_id][]	= $forum;
}
unset($AllForums);

$userPostViews	= SiteUser::is_login() ? $USER->getAllPostViews() : array();

function displayForumList($forumID=0) {
	global $Forums, $userPostViews;
	if( empty($Forums[$forumID]) ) { return; }
	echo <<<EOF
<div class="forumlist" id="forumlist-{$forumID}">
EOF;
	foreach( $Forums[$forumID] as $forum ) {
		/* @var $forum Forum */
		echo '
	<div class="panel panel-default">
		<div class="panel-heading" data-id="'.$forum->id().'">
			<div class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#forumlist-0" href="#collapse-'.$forum->id().'">'.$forum.'</a>
			</div>
		</div>
		<div id="collapse-'.$forum->id().'" class="panel-collapse collapse in">
			<div class="panel-body">';
		displayForumList($forum->id());
		echo '
				<div class="threadWrapper">
					<h4>Threads of '.$forum.'</h4>
					<ul>';
		foreach( $forum->getPosts() as $post ) {
			$viewed	= isset($userPostViews[$post->id()]) && $userPostViews[$post->id()]->isViewedAfter($post);
			echo '
						<li><div class="icon-set"><span class="icon-eye-'.($viewed ? 'open' : 'close').'"></span></div><a href="'.$post->getLink().'">'.$post.'</a><span class="thread_infos"><a href="#">Last message</a> by <a href="#">T1b0</a>, at 21/05/2013</span></li>';
		}
// 						<li><div class="icon-set"><span class="icon-eye-open"></span></div><a href="#">What is your last favorite films ?</a><span class="thread_infos"><a href="#">Last message</a> by <a href="#">Math1</a>, at 19/05/2013</span></li>
// 						<li><div class="icon-set"><span class="icon-eye-open"></span></div><a href="#">The death of Michael Jackson</a><span class="thread_infos"><a href="#">Last message</a> by <a href="#">T1b0</a>, at 19/05/2013</span></li>';
		echo '
					</ul>
				</div>
			</div>
		</div>
	</div>';
	}
	echo '
</div>';
EOF;
}

displayForumList();

/*
<div class="forumlist" id="forumlist-0">
	<div class="panel panel-default">
		<div class="panel-heading" data-id="1">
			<div class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#forumlist-0" href="#collapse-1">
				Spare-time Forum (3/9)
				</a>
			</div>
		</div>
		<div id="collapse-1" class="panel-collapse collapse in">
			<div class="panel-body">
			
				<div class="forumlist" id="forumlist-1">
					<div class="panel panel-default">
						<div class="panel-heading"  data-id="3" data-label="Literacy Of Idiots Forum">
							<div class="panel-title">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#forumlist-1" href="#collapse-3">
								Literacy Of Idiots Forum (2/3)
								</a>
							</div>
						</div>
						<div id="collapse-3" class="panel-collapse collapse in">
							<div class="panel-body">
								<div class="threadWrapper">
									<a class="btn btn-default btn-sm right newthreadbtn"><i class="icon-plus"></i> New thread</a>
									
									<h4>Threads of Literacy Of Idiots Forum</h4>
									<ul>
										<li class="unread"><div class="icon-set"><span class="icon-eye-open"></span></div><a href="#">How to go so far with only a bukket ?</a><span class="thread_infos"><a href="#">Last message</a> by <a href="#">T1b0</a>, at 02/06/2013</span></li>
										<li class="unread"><div class="icon-set"><span class="icon-eye-open"></span></div><a href="#">What is your last favorite films ?</a><span class="thread_infos"><a href="#">Last message</a> by <a href="#">Math1</a>, at 19/05/2013</span></li>
										<li class="read"><div class="icon-set"><span class="icon-eye-open"></span></div><a href="#">The death of Michael Jackson</a><span class="thread_infos"><a href="#">Last message</a> by <a href="#">T1b0</a>, at 19/05/2013</span></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading forum-entry">
							<div class="panel-title">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#forumlist-1" href="#collapse-4">
								The Timeless Beer Forum (0/3)
								</a>
							</div>
						</div>
						<div id="collapse-4" class="panel-collapse collapse">
							<div class="panel-body">
								<div class="threadWrapper">
									<h4>Threads of The Timeless Beer Forum</h4>
									<ul>
										<li class="unread"><a href="#">Where to go for holidays ?</a></li>
										<li class="unread"><a href="#">What is your last favorite films ?</a></li>
										<li class="read"><a href="#">The death of Michael Jackson</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="threadWrapper">
					<h4>Threads of Spare-time Forum (1/3)</h4>
					<ul>
						<li><div class="icon-set"><span class="icon-eye-close"></span></div><a href="#">Where to go for holidays ?</a><span class="thread_infos"><a href="#">Last message</a> by <a href="#">T1b0</a>, at 21/05/2013</span></li>
						<li><div class="icon-set"><span class="icon-eye-open"></span></div><a href="#">What is your last favorite films ?</a><span class="thread_infos"><a href="#">Last message</a> by <a href="#">Math1</a>, at 19/05/2013</span></li>
						<li><div class="icon-set"><span class="icon-eye-open"></span></div><a href="#">The death of Michael Jackson</a><span class="thread_infos"><a href="#">Last message</a> by <a href="#">T1b0</a>, at 19/05/2013</span></li>
						<!--
						<li><a href="#">Where to go for holidays ?</a><a href="#" class="thread_infos">Last message by T1b0, at 21/05/2013</a></li>
						<li><a href="#">What is your last favorite films ?</a><a href="#" class="thread_infos">Last message by Math1, at 19/05/2013</a></li>
						<li><a href="#">The death of Michael Jackson</a><a href="#" class="thread_infos">Last message by T1b0, at 19/05/2013</a></li>
						-->
					</ul>
				</div>
				
				
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading forum-entry">
			<div class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#forumlist-0" href="#collapse-2">
				Anti-Racist Forum (0/3)
				</a>
			</div>
		</div>
		<div id="collapse-2" class="panel-collapse collapse">
		<div class="panel-body">
			<div class="threadWrapper">
				<h4>Threads of Anti-Racist Forum</h4>
				<ul>
					<li><a href="#">Where to go for holidays ?</a></li>
					<li><a href="#">What is your last favorite films ?</a></li>
					<li><a href="#">The death of Michael Jackson</a></li>
				</ul>
			</div>
		</div>
		</div>
	</div>
</div>
*/
?>

<div class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Modal title</h4>
      </div>
      <div class="modal-body">
        <p>One fine body&hellip;</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div id="connectForm" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="myModalLabel">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" class="form-horizontal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="myModalLabel">Log in</h3>
	</div>
	<div class="modal-body" style="text-align: center;">
		<div class="control-group">
			<label class="control-label" for="inputLogin">Username / Email</label>
<!-- 			<div class="controls"> -->
<!-- 				<input type="text" name="data[login]" id="inputLogin" placeholder="Enter your ID"> -->
<!-- 			</div> -->
			<input class="form-control" type="text" name="data[login]" id="inputLogin" placeholder="Enter your ID">
		</div>
		<div class="control-group">
			<label class="control-label" for="inputPassword">Password</label>
			<input class="form-control" type="password" name="data[password]" id="inputPassword" placeholder="Enter your password">
		</div>
		<div class="control-group">
			<button id="registerBtn" class="btn btn-link" style="margin: 0 0 0 350px;" data-toggle="modal" data-target="#registerForm">Register</button>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button type="submit" name="submitLogin" class="btn btn-primary">Connect</button>
	</div>
</form>
</div>
</div>
</div>

<div id="registerForm" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" class="form-horizontal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="myModalLabel">Register</h3>
	</div>
	<div class="modal-body" style="text-align: center;">
		<div class="control-group">
			<label class="control-label" for="inputName">Your Username</label>
			<input class="form-control" type="text" name="data[name]" id="inputName" placeholder="Enter your name">
		</div>
		<div class="control-group">
			<label class="control-label" for="inputEmail">Your Email</label>
			<input class="form-control" type="text" name="data[email]" id="inputEmail" placeholder="Enter your email">
		</div>
		<div class="control-group">
			<label class="control-label" for="inputPassword">Your Password</label>
			<input class="form-control" type="password" name="data[password]" id="inputPassword" placeholder="Enter your password">
		</div>
		<div class="control-group">
			<label class="control-label" for="inputConfPassword">Confirm password</label>
			<input class="form-control" type="password" name="data[password_conf]" id="inputConfPassword" placeholder="Enter your password">
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button type="submit" name="submitRegister" class="btn btn-primary">Register</button>
	</div>
</form>
</div>
</div>
</div>

<div id="newThreadForm" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<!-- <form method="POST" class=""> -->
<!-- form-horizontal -->
	<input type="hidden" name="data[tid]" id="ntf_fid" />
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="myModalLabel">Create new Thread</h3>
	</div>
	<div class="modal-body" style="text-align: center;">
		<h3 id="ntf_title"></h3>
		<div class="control-group">
			<label class="control-label" for="inputName">Title</label>
			<input class="form-control" type="text" name="data[name]" id="inputName" placeholder="Enter the title of your new thread">
		</div>
		
<!-- 	#editor	#newThreadForm textarea -->
		<div class="btn-toolbar" data-role="editor-toolbar" data-target="#editor">
			<div class="btn-group">
				<a class="btn dropdown-toggle" data-toggle="dropdown" title="Font"><i class="icon-font"></i><b class="caret"></b></a>
				<ul class="dropdown-menu"></ul>
			</div>
			<div class="btn-group">
				<a class="btn dropdown-toggle" data-toggle="dropdown" title="Font Size"><i class="icon-text-height"></i>&nbsp;<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a data-edit="fontSize 5"><font size="5">Huge</font></a></li>
					<li><a data-edit="fontSize 3"><font size="3">Normal</font></a></li>
					<li><a data-edit="fontSize 1"><font size="1">Small</font></a></li>
				</ul>
			</div>
			<div class="btn-group">
				<a class="btn" data-edit="bold" title="Bold (Ctrl/Cmd+B)"><i class="icon-bold"></i></a>
				<a class="btn" data-edit="italic" title="Italic (Ctrl/Cmd+I)"><i class="icon-italic"></i></a>
				<a class="btn" data-edit="strikethrough" title="Strikethrough"><i class="icon-strikethrough"></i></a>
				<a class="btn" data-edit="underline" title="Underline (Ctrl/Cmd+U)"><i class="icon-underline"></i></a>
			</div>
			<div class="btn-group">
				<a class="btn" data-edit="insertunorderedlist" title="Bullet list"><i class="icon-list-ul"></i></a>
				<a class="btn" data-edit="insertorderedlist" title="Number list"><i class="icon-list-ol"></i></a>
				<a class="btn" data-edit="outdent" title="Reduce indent (Shift+Tab)"><i class="icon-indent-left"></i></a>
				<a class="btn" data-edit="indent" title="Indent (Tab)"><i class="icon-indent-right"></i></a>
			</div>
			<div class="btn-group">
				<a class="btn" data-edit="justifyleft" title="Align Left (Ctrl/Cmd+L)"><i class="icon-align-left"></i></a>
				<a class="btn" data-edit="justifycenter" title="Center (Ctrl/Cmd+E)"><i class="icon-align-center"></i></a>
				<a class="btn" data-edit="justifyright" title="Align Right (Ctrl/Cmd+R)"><i class="icon-align-right"></i></a>
				<a class="btn" data-edit="justifyfull" title="Justify (Ctrl/Cmd+J)"><i class="icon-align-justify"></i></a>
			</div>
			<div class="btn-group">
				<button class="btn dropdown-toggle" data-toggle="dropdown" title="Hyperlink"><i class="icon-link"></i></button>
				<div class="dropdown-menu input-append">
					<input class="span2" placeholder="URL" type="text" data-edit="createLink"/>
					<button class="btn" type="button">Add</button>
				</div>
				<a class="btn" data-edit="unlink" title="Remove Hyperlink"><i class="icon-cut"></i></a>
			</div>
			<div class="btn-group">
				<a class="btn" title="Insert picture (or just drag & drop)" id="pictureBtn"><i class="icon-picture"></i></a>
				<input type="file" data-role="magic-overlay" data-target="#pictureBtn" data-edit="insertImage" />
			</div>
			<div class="btn-group">
				<a class="btn" data-edit="undo" title="Undo (Ctrl/Cmd+Z)"><i class="icon-undo"></i></a>
				<a class="btn" data-edit="redo" title="Redo (Ctrl/Cmd+Y)"><i class="icon-repeat"></i></a>
			</div>
			<input type="text" data-edit="inserttext" id="voiceBtn" x-webkit-speech="">
		</div>
		<div id="editor">Go ahead&hellip;</div>
<!-- 		<textarea name="data[message]" placeholder="Enter your message here..."></textarea> -->
<!-- 		<div class="control-group"> -->
<!-- 			<label class="control-label" for="inputMessage"></label> -->
<!-- 			<div class="controls"> -->
<!-- 				<textarea name="data[message]" id="inputMessage" placeholder="Enter your message here..."></textarea> -->
<!-- 			</div> -->
<!-- 		</div> -->
	</div>
	<div class="modal-footer">
		<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button type="submit" name="submitCreateThread" class="btn btn-primary">Save</button>
	</div>
<!-- </form> -->
</div>
</div>
</div>

<div id="newForumForm" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" class="">
<!-- form-horizontal -->
	<input type="hidden" name="data[fid]" id="nff_fid" />
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="myModalLabel">Create new Forum</h3>
	</div>
	<div class="modal-body" style="text-align: center;">
		<h3 id="ntf_title"></h3>
		<div class="control-group">
			<label class="control-label" for="inputName">Title</label>
			<input class="form-control" type="text" name="data[name]" id="inputName" placeholder="Enter the title of your new forum">
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button type="submit" name="submitCreateForum" class="btn btn-primary">Save</button>
	</div>
</form>
</div>
</div>
</div>

<style>
body.editor {
	background-color: #D9D9D9;
/* 	background-color: #F0F0F0; */
}

body {
    overflow: hidden;
}
body > .container {
    height: 500px;
    min-height: 400px;
    overflow-y: scroll;
}

/* #editor {overflow:scroll; max-height:300px} */
/* #newThreadForm { */
/* 	width: 900px;  */
/* 	margin-left: -450px;  */
/* } */
/* #newThreadForm label { */
/* 	width: 100px;  */
/* } */
/* #newThreadForm .controls { */
/* 	margin-left: 100px; */
/* } */
/* #newThreadForm input { */
/* 	width: 306px; */
/* } */
/* #newThreadForm textarea { */
/* 	width: 406px; */
/* 	height: 250px; */
/* 	overflow:scroll; */
/* } */
/* #editor { */
/* #newThreadForm textarea { */
#editor {
	max-height: 250px;
	height: 250px;
	background-color: white;
	border-collapse: separate; 
	border: 1px solid rgb(204, 204, 204); 
	padding: 4px; 
	box-sizing: content-box; 
	-webkit-box-shadow: rgba(0, 0, 0, 0.0745098) 0px 1px 1px 0px inset; 
	box-shadow: rgba(0, 0, 0, 0.0745098) 0px 1px 1px 0px inset;
	border-top-right-radius: 3px; border-bottom-right-radius: 3px;
	border-bottom-left-radius: 3px; border-top-left-radius: 3px;
	overflow: scroll;
	outline: none;
}
#voiceBtn {
  width: 20px;
  color: transparent;
  background-color: transparent;
  transform: scale(2.0, 2.0);
  -webkit-transform: scale(2.0, 2.0);
  -moz-transform: scale(2.0, 2.0);
  border: transparent;
  cursor: pointer;
  box-shadow: none;
  -webkit-box-shadow: none;
}
div[data-role="editor-toolbar"] {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.dropdown-menu a {
  cursor: pointer;
}

.cover {
	width: 100%;
	height: 100%;
	position: fixed;
	top: 0;
	left: 0;
/* 	opacity: 0.5; */
	background: #FFFFFF;
	z-index: 1500;
}
</style>

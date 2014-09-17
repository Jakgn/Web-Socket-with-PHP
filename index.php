<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8' />
<style type="text/css">
<!--
.chat_wrapper {
	width: 500px;
	margin-right: auto;
	margin-left: auto;
	background: #CCCCCC;
	border: 1px solid #999999;
	padding: 10px;
	font: 12px 'lucida grande',tahoma,verdana,arial,sans-serif;
}
.chat_wrapper .message_box {
	background: #FFFFFF;
	height: 150px;
	overflow: auto;
	padding: 10px;
	border: 1px solid #999999;
}
.chat_wrapper .panel input{
	padding: 2px 2px 2px 5px;
}
.system_msg{color: #BDBDBD;font-style: italic;}
.user_name{font-weight:bold;}
.user_message{color: #88B6E0;}
-->
</style>
</head>
<body>	
<?php 
$colours = array('007AFF','FF7000','FF7000','15E25F','CFC700','CFC700','CF1100','CF00BE','F00');
$user_colour = array_rand($colours);
?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>

<script language="javascript" type="text/javascript">  
var user_name;
function show_prompt(){  
	user_name = prompt('輸入你的名字：', '');
	if(!user_name){  
		alert('姓名不能是空白,請輸入名字！');  
		show_prompt();
	}
}  
$(document).ready(function(){
	//create a new WebSocket object.
	var wsUri = "ws://localhost:9000/webSocket/server.php";	
	websocket = new WebSocket(wsUri); 

	websocket.onopen = function(ev) { // connection is open 
		show_prompt();
		var msg = {
			'type': 'inChat',
			'name': user_name,
		};
		websocket.send(JSON.stringify(msg));

		$('#message_box').append("<div class=\"system_msg\">Connected!</div>"); //notify user
	}

	$('#send-btn').click(function(){ //use clicks message send button	
		var mymessage = $('#message').val(); //get message text
		var talkname = $('#name').find(':selected').val();//get user want to talks name

		if(talkname == ""){ //empty name?
			alert("Enter talk Name please!");
			return;
		}
		if(mymessage == ""){ //emtpy message?
			alert("Enter Some message Please!");
			return;
		}

		//prepare json data
		var msg = {
			'type': 'chatting',
			'message': mymessage,
			'name': user_name,
			'talkto': talkname,
			'color' : '<?php echo $colours[$user_colour]; ?>'
		};
		//convert and send data to server
		websocket.send(JSON.stringify(msg));

		$('#message').val(''); //reset text
	});

	//#### Message received from server?
	websocket.onmessage = function(ev) {
		var msg = JSON.parse(ev.data); //PHP sends Json data
		var type = msg.type; //message type
		var umsg = msg.message; //message text
		
		if(type == 'system'){
			$('#message_box').append("<div class=\"system_msg\">"+umsg+"</div>");
		}else if(type == 'list'){ //for update user list
			var num_list = msg.num;
			$('#name option').remove();	
			$('#name').append($("<option></option>").attr("value", "all").text("all"));
			for(var i=0;i<num_list;i++){
				var name = msg[i];
				$('#name').append($("<option></option>").attr("value", name).text(name));
			}

		}else{
			var uname = msg.name; //user name
			var ucolor = msg.color; //color
			if(type == 'usermsg') { //talk to everybody
				$('#message_box').append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
			}else if(type == 'personmsg'){ //talk to someone
				var talk_to = msg.talkto;
				$('#message_box').append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+" >> "+talk_to+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
			}
		}
		

	};

	websocket.onerror	= function(ev){$('#message_box').append("<div class=\"system_error\">Error Occurred - "+ev.data+"</div>");}; 
	websocket.onclose 	= function(ev){$('#message_box').append("<div class=\"system_msg\">Connection Closed</div>");}; 
});
</script>
<div class="chat_wrapper">
<div class="message_box" id="message_box"></div>
<div class="panel">
<select id="name">
</select>
<!--<input type="text" name="name" id="name" placeholder="Your Name" maxlength="10" style="width:20%"  />-->
<input type="text" name="message" id="message" placeholder="Message" maxlength="80" style="width:60%" />
<button id="send-btn">Send</button>
</div>
</div>

</body>
</html>

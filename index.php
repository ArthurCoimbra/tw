<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	
	<title></title>
	<link rel="stylesheet" href="">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
            integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
            crossorigin="anonymous"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.8/css/bootstrap-material-design.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.8/css/ripples.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.8/js/material.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.8/js/ripples.min.js"></script>
	
	<script src="go.js"></script>
	
	<script id="code">
  function init() {
    if (window.goSamples) goSamples();  // init for these samples -- you don't need to call this
    var $$ = go.GraphObject.make;  // for conciseness in defining templates
    myDiagram =
      $$(go.Diagram, "myDiagramDiv",  // must name or refer to the DIV HTML element
        {
          // start everything in the middle of the viewport
          initialContentAlignment: go.Spot.Center,
          // have mouse wheel events zoom in and out instead of scroll up and down
          "toolManager.mouseWheelBehavior": go.ToolManager.WheelZoom,
          // support double-click in background creating a new node
          "clickCreatingTool.archetypeNodeData": { text: "new node" },
          // enable undo & redo
          "undoManager.isEnabled": true
        });
    // when the document is modified, add a "*" to the title and enable the "Save" button
    myDiagram.addDiagramListener("Modified", function(e) {
      var button = document.getElementById("SaveButton");
      if (button) button.disabled = !myDiagram.isModified;
      var idx = document.title.indexOf("*");
      if (myDiagram.isModified) {
        if (idx < 0) document.title += "*";
      } else {
        if (idx >= 0) document.title = document.title.substr(0, idx);
      }
    });
    // define the Node template
    myDiagram.nodeTemplate =
      $$(go.Node, "Auto",
        new go.Binding("location", "loc", go.Point.parse).makeTwoWay(go.Point.stringify),
        // define the node's outer shape, which will surround the TextBlock
        $$(go.Shape, "RoundedRectangle",
          {
            parameter1: 20,  // the corner has a large radius
            fill: $$(go.Brush, "Linear", { 0: "rgb(254, 201, 0)", 1: "rgb(254, 162, 0)" }),
            stroke: null,
            portId: "",  // this Shape is the Node's port, not the whole Node
            fromLinkable: true, fromLinkableSelfNode: true, fromLinkableDuplicates: true,
            toLinkable: true, toLinkableSelfNode: true, toLinkableDuplicates: true,
            cursor: "pointer"
          }),
        $$(go.TextBlock,
          {
            font: "bold 11pt helvetica, bold arial, sans-serif",
            editable: true  // editing the text automatically updates the model data
          },
          new go.Binding("text").makeTwoWay())
      );
    // unlike the normal selection Adornment, this one includes a Button
    myDiagram.nodeTemplate.selectionAdornmentTemplate =
      $$(go.Adornment, "Spot",
        $$(go.Panel, "Auto",
          $$(go.Shape, { fill: null, stroke: "blue", strokeWidth: 2 }),
          $$(go.Placeholder)  // a Placeholder sizes itself to the selected Node
        ),
        // the button to create a "next" node, at the top-right corner
        $$("Button",
          {
            alignment: go.Spot.TopRight,
            click: addNodeAndLink  // this function is defined below
          },
          $$(go.Shape, "PlusLine", { width: 6, height: 6 })
        ) // end button
      ); // end Adornment
    // clicking the button inserts a new node to the right of the selected node,
    // and adds a link to that new node
    function addNodeAndLink(e, obj) {
      var adornment = obj.part;
      var diagram = e.diagram;
      diagram.startTransaction("Add State");
      // get the node data for which the user clicked the button
      var fromNode = adornment.adornedPart;
      var fromData = fromNode.data;
      // create a new "State" data object, positioned off to the right of the adorned Node
      var toData = { text: "new" };
      var p = fromNode.location.copy();
      p.x += 200;
      toData.loc = go.Point.stringify(p);  // the "loc" property is a string, not a Point object
      // add the new node data to the model
      var model = diagram.model;
      model.addNodeData(toData);
      // create a link data from the old node data to the new node data
      var linkdata = {
        from: model.getKeyForNodeData(fromData),  // or just: fromData.id
        to: model.getKeyForNodeData(toData),
        text: "transition"
      };
      // and add the link data to the model
      model.addLinkData(linkdata);
      // select the new Node
      var newnode = diagram.findNodeForData(toData);
      diagram.select(newnode);
      diagram.commitTransaction("Add State");
      // if the new node is off-screen, scroll the diagram to show the new node
      diagram.scrollToRect(newnode.actualBounds);
    }
    // replace the default Link template in the linkTemplateMap
    myDiagram.linkTemplate =
      $$(go.Link,  // the whole link panel
        {
          curve: go.Link.Bezier, adjusting: go.Link.Stretch,
          reshapable: true, relinkableFrom: true, relinkableTo: true,
          toShortLength: 3
        },
        new go.Binding("points").makeTwoWay(),
        new go.Binding("curviness"),
        $$(go.Shape,  // the link shape
          { strokeWidth: 1.5 }),
        $$(go.Shape,  // the arrowhead
          { toArrow: "standard", stroke: null }),
        $$(go.Panel, "Auto",
          $$(go.Shape,  // the label background, which becomes transparent around the edges
            {
              fill: $$(go.Brush, "Radial",
                      { 0: "rgb(240, 240, 240)", 0.3: "rgb(240, 240, 240)", 1: "rgba(240, 240, 240, 0)" }),
              stroke: null
            }),
          $$(go.TextBlock, "transition",  // the label text
            {
              textAlign: "center",
              font: "9pt helvetica, arial, sans-serif",
              margin: 4,
              editable: true  // enable in-place editing
            },
            // editing the text automatically updates the model data
            new go.Binding("text").makeTwoWay())
        )
      );
    // read in the JSON data from the "mySavedModel" element
    load();
  }
  // Show the diagram's model in JSON format
  function save() {
	
    document.getElementById("mySavedModel").value = myDiagram.model.toJson();
  }
  function load() {
    myDiagram.model = go.Model.fromJson(document.getElementById("mySavedModel").value);
  }
</script>
</head>
<body onload="init()"> 
	
	<div id="status"></div>
	<button id="btntw">Post to Twitter</button>
	<button id="btnfb">Post to Facebook</button>
	<button id="btnit">Post to Instagram</button>

	<p style="display: none" id="p1">
	Entre com o texto desejado e clique em ok:
	<input type="text" id="txtBox">
	<button id="okbtn" style="display:none">Ok</button>
	<button id="okbtn2" style="display:none">Ok</button>
	<button id="okbtn3" style="display:none">Ok</button>
	</p>
	
	
	<div id="myDiagramDiv" style="border: solid 1px black; width: 100%; height: 400px"></div>
	<script type="text/javascript">
	
	var janelanova;
	//Buttons
		$( "#btnfb" ).click(function() {
			$( "#p1" ).show();
			$("#btnfb").hide();
			$("#okbtn").show();
		});
		
		$( "#btntw" ).click(function() {
			$( "#p1" ).show();
			$("#btntw").hide();
			$("#okbtn2").show();
		});
		
		$( "#btnit" ).click(function() {
			$( "#p1" ).show();
			$("#btnit").hide();
			$("#okbtn3").show();
		});
		
		$("#okbtn").click(function() {
			var valortexto = $("#txtBox").val();
			login(valortexto);
			$("#p1").hide();
			$("#btnfb").show();
			$("#okbtn").hide();
		});
		
		$("#okbtn3").click(function() {
			loginInst();
			$("#p1").hide();
			$("#btnit").show();
			$("#okbtn3").hide();
		});
		
		//Instagram
		var accessToken1 = null;
		var authenticateInstagram = function(instagramClientId, instagramRedirectUri, callback) {
			//the pop-up window size
			var popupWidth = 700,
				popupHeight = 500,
				popupLeft = (window.screen.width - popupWidth) / 2,
				popupTop = (window.screen.height - popupHeight) / 2;
			//the url needs to point to instagram_auth.php
			var popup = window.open('instagram_auth.php', '', 'width='+popupWidth+',height='+popupHeight+',left='+popupLeft+',top='+popupTop+'');
			popup.onload = function() {
				//open authorize url in pop-up
				if(window.location.hash.length == 0) {
					popup.open('https://instagram.com/oauth/authorize/?client_id='+instagramClientId+'&redirect_uri='+instagramRedirectUri+'&response_type=token', '_self');
				}
				//an interval runs to get the access token from the pop-up
				var interval = setInterval(function() {
					try {
						//check if hash exists
						if(popup.location.hash.length) {
							//hash found, that includes the access token
							clearInterval(interval);
							accessToken1 = popup.location.hash.slice(14); //slice #access_token= from string
							popup.close();
							if(callback != undefined && typeof callback == 'function') callback();
						}
					}
					catch(evt) {
						//permission denied
					}
				}, 100);
			}
		};
		
		function uploadInst(msg) {
			var img = myDiagram.makeImage();
			var imageData = img.src;
			
			var fd = new FormData();
			console.log(accessToken1);
			fd.append("access_token",accessToken1);
			fd.append("source", imageData);
			fd.append("message",msg);
			try {
		
				$.ajax({
				url:"" + accessToken1, //Essa URL não existe no Instagram, não há como fazer upload de imagens por política da empresa
				type:"POST",
				data:fd,
				processData:false,
				contentType:false,
				cache:false,
				success:function(data){
					console.log("success " + data);
				},
				error:function(shr,status,data){
					console.log("error " + data + " Status " + shr.status);
				},
				complete:function(){
					console.log("Posted to Instagram");
				}
				});
				
				
			}
			catch(e) {
				console.log(e);
			}
		}
		
		function login_callback() {
			alert("You are successfully logged in! Access Token: "+accessToken1);
		}
		
		function loginInst() {
			authenticateInstagram(
			    '33356aaf8038460398f9f5fead13b66a', //instagram client ID
			    'http://localhost/tw', //instagram redirect URI
				//uploadInst("oi");
			    //login_callback //optional - a callback function
			);
			return false;
		}
	
	
	//Facebook
		var accessToken;
		// initialize and setup facebook js sdk
		window.fbAsyncInit = function() {
		    FB.init({
		      appId      : '1829645957278469',
		      xfbml      : true,
		      version    : 'v2.8'
		    });
		};
		(function(d, s, id){
		    var js, fjs = d.getElementsByTagName(s)[0];
		    if (d.getElementById(id)) {return;}
		    js = d.createElement(s); js.id = id;
		    js.src = "//connect.facebook.net/en_US/sdk.js";
		    fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
		
		// login with facebook with extended publish_actions permission
		function login(msg) {
			FB.login(function(response) {
				if (response.status === 'connected') {
		    		accessToken= response.authResponse.accessToken;
					uploadPhoto(msg);
		    	} else if (response.status === 'not_authorized') {
		    		document.getElementById('status').innerHTML = 'We are not logged in.'
		    	} else {
		    		document.getElementById('status').innerHTML = 'You are not logged into Facebook.';
		    	}
			}, {scope: 'publish_actions'});
		}
		
		// uploading photo on user timeline
		function uploadPhoto(msg) {
			var img = myDiagram.makeImage();
			var imageData = img.src;
			
			try {
				blob = dataURItoBlob(imageData);
			}
			catch(e) {
				console.log(e);
			}
			var fd = new FormData();
			console.log(accessToken);
			fd.append("access_token",accessToken);
			fd.append("source", blob);
			fd.append("message",msg);
			try {
		
				$.ajax({
				url:"https://graph.facebook.com/me/photos?access_token=" + accessToken,
				type:"POST",
				data:fd,
				processData:false,
				contentType:false,
				cache:false,
				success:function(data){
					console.log("success " + data);
				},
				error:function(shr,status,data){
					console.log("error " + data + " Status " + shr.status);
				},
				complete:function(){
					console.log("Posted to facebook");
				}
				});
				
				
			}
			catch(e) {
				console.log(e);
			}
		}
	function dataURItoBlob(dataURI) {
			var byteString = atob(dataURI.split(',')[1]);
			var ab = new ArrayBuffer(byteString.length);
			var ia = new Uint8Array(ab);
			for (var i = 0; i < byteString.length; i++) {
				ia[i] = byteString.charCodeAt(i);
			}
		return new Blob([ab], { type: 'image/png' });
	}
	
	
	//Twitter
    (function () {
        
        // Twitter oauth handler
        $.oauthpopup = function (options) {
            if (!options || !options.path) {
                throw new Error("options.path must not be empty");
            }
            options = $.extend({
                windowName: 'ConnectWithOAuth'
                , windowOptions: 'location=0,status=0,width=800,height=400'
                , callback: function () {
                    debugger;
                }
            }, options);

            var oauthWindow = window.open(options.path, options.windowName, options.windowOptions);
            var oauthInterval = window.setInterval(function () {
                if (oauthWindow.closed) {
                    window.clearInterval(oauthInterval);
                    options.callback();
                }
            }, 1000);
        };
        // END Twitter oauth handler

        //bind to element and pop oauth when clicked
        $.fn.oauthpopup = function (options) {
            $this = $(this);
            $this.click($.oauthpopup.bind(this, options));
        };

        $('#okbtn2').click(function () {
			var valortexto = $("#txtBox").val();
			var img = myDiagram.makeImage();
			var dataURL = img.src;
            $.oauthpopup({
                path: './auth/twitter.php', //leva para o codigo que faz a autenticação
                callback: function () {
                    console.log(window.twit);
                    var data = new FormData();
                    // Tweet text
                    data.append('status', valortexto);
                    // Binary image
                    data.append('image', dataURL);
                    // oAuth Data
                    data.append('oauth_token', window.twit.oauth_token);
                    data.append('oauth_token_secret', window.twit.oauth_token_secret);
                    // Post to Twitter as an update with

                    return $.ajax({
                        url: './auth/share-on-twitter.php',
                        type: 'POST',
                        data: data,
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            console.log('Posted to Twitter.');
                            console.log(data);
                        }
                    });
                }
            });
			$("#p1").hide();
			$("#btntw").show();
			$("#okbtn2").hide();
        });
	})();

	</script>
    <div>
      Diagram Model saved in JSON format:
    </div>
    <textarea id="mySavedModel" style="width:100%;height:300px">
{ "nodeKeyProperty": "id",
  "nodeDataArray": [
    { "id": 0, "loc": "120 120", "text": "Initial" },
    { "id": 1, "loc": "330 120", "text": "First down" },
    { "id": 2, "loc": "226 376", "text": "First up" },
    { "id": 3, "loc": "60 276", "text": "Second down" },
    { "id": 4, "loc": "226 226", "text": "Wait" }
  ],
  "linkDataArray": [
    { "from": 0, "to": 0, "text": "up or timer", "curviness": -20 },
    { "from": 0, "to": 1, "text": "down", "curviness": 20 },
    { "from": 1, "to": 0, "text": "up (moved)\nPOST", "curviness": 20 },
    { "from": 1, "to": 1, "text": "down", "curviness": -20 },
    { "from": 1, "to": 2, "text": "up (no move)" },
    { "from": 1, "to": 4, "text": "timer" },
    { "from": 2, "to": 0, "text": "timer\nPOST" },
    { "from": 2, "to": 3, "text": "down" },
    { "from": 3, "to": 0, "text": "up\nPOST\n(dblclick\nif no move)" },
    { "from": 3, "to": 3, "text": "down or timer", "curviness": 20 },
    { "from": 4, "to": 0, "text": "up\nPOST" },
    { "from": 4, "to": 4, "text": "down" }
  ]
}
    </textarea>
  </div>
</body>
</html>

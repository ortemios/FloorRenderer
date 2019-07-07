<html>
    <head>
        <meta charset="utf-8">
        <title></title>
		<script src="js/jquery.min.js"></script>
        <script src="js/jquery.redirect.js"></script>
        <link rel="stylesheet" type="text/css" href="styles/1_style.css">
        <link rel="stylesheet" type="text/css" href="styles/2_style.css">
    </head>
    <body>

        <div class="modal" id="main">
            <div class="modal_content">
                <div class="tab">
                    <button class="tablinks" onclick="openCity(event, 'Samples')">Use Sample Rooms</button>
                    <button class="tablinks" onclick="openCity(event, 'upload')" id="defaultOpen">UPLOAD MY OWN PHOTO</button>
                    <button class="tablinks" onclick="openCity(event, 'saved')">VIEW MY SAVED ROOMS</button>
                </div>

                <!-- Tab content -->
                <div id="Samples" class="tabcontent">
                    <h3>Some content</h3>
                    <p>London is the capital city of England.</p>
                </div>

                <div id="upload" class="tabcontent">
                    <div class="uploading">
                        <h3>Upload Your Own Photo</h3>
                        <p>Take a photo of your room or choose a photo saved on your device.</p>
                        
                        <button class="get_started_btn" onclick="file.click();">GET STARTED</button>
                        <form action="upload_image.php" method="POST" enctype="multipart/form-data">
                            <input type="file" name="image" id="file_selection" onchange="fileSelected();" style="display:none">
                            <input type="submit" id="submit_btn" style="display:none">
                        </form>
                    </div>

                    <div class="examples">
                        <div class="img_and_text">
                            <div class="example_img">
                                <img src="img/good_photo.png" style="width:100%">
                                <div class="text">
                                    <p>GOOD PHOTO</p>
                                </div>
                            </div>
                            <div class="example_img">
                                <img src="img/bad_photo.png" style="width:100%">
                                <div class="text">
                                    <p>BAD PHOTO</p>
                                </div>
                            </div>
                        </div>
                        <div class="underexampled_text">
                            <p> <img src="img/star.png"> Use good and bad photos above as a guideline for best results. Photos of your room should have a minimal amount of objects so you can easily draw over the surface you want in the room.</p>
                        </div>
                    </div>


                </div>

                <div id="saved" class="tabcontent">
                    <h3>Tokyo</h3>
                    <p>Tokyo is the capital of Japan.</p>
                </div>
            </div>
        </div>
        
        <div id="preview" style="display: none">
            <div class="main">
                <div class="outter_img">
                    <div class="inner_img">
                        <img id="preview_img">
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="btns">
                    <input class="upload_btn" type="button" onclick="back();" value="Upload a Different Image"></input>
                    <input class="proceed_btn" type="button" onclick="submit();" value="Proceed to Next Step"></button>
                </div>
            </div>
        </div>

        <script>
            var file = document.getElementById("file_selection");
            var mainContent = document.getElementById("main");
            var preview = document.getElementById("preview");
			
			function submit()
			{
				var img = new Image;
				img.onload = function()
				{
					var canvas = document.createElement("canvas");
					canvas.style = "display: none";
					document.body.appendChild(canvas);
					
					var ratio = img.height/img.width;
					canvas.width = Math.min(img.width, 2100);
					canvas.height = canvas.width*ratio;
					var ctx = canvas.getContext("2d");
					ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
					
					var dataURL = canvas.toDataURL();
					$.ajax({
						type: "POST",
						url: "upload_image.php",
						data: {
							image: dataURL
						}
					}).done(function(resp) {
						var room_name = resp;
						$.redirect("floor_remover.php", { room_name: room_name });
					});
				}
				img.src = URL.createObjectURL(file.files[0]);
			}
            
            function fileSelected()
            {
                mainContent.style = "display: none";
                preview.style = "";
                var fr = new FileReader();
                fr.onload = function () 
                {
                    document.getElementById("preview_img").src = fr.result;
                };
                fr.readAsDataURL(file.files[0]);
            }
            function back()
            {
                mainContent.style = "";
                preview.style = "display: none";
            }
            function openCity(evt, cityName) 
            {
                var i, tabcontent, tablinks;
                tabcontent = document.getElementsByClassName("tabcontent");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }
                tablinks = document.getElementsByClassName("tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                document.getElementById(cityName).style.display = "block";
                evt.currentTarget.className += " active";
            }
            
            document.getElementById("defaultOpen").click();
        </script>
    </body>
</html>


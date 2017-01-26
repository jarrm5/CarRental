$(document).ready(init);

function init(){
    $("#search-button").on("click",login);
    $("#password-input").on("keydown",function(event){maybe_login(event);});
}

function maybe_login(event){
    if (event.keyCode == 13) //ENTER KEY
        login();
}

function login() {
    $("#loading").attr("class","loading");//show the loading icon
    
    $.ajax({
        method: "POST",
        url: "server/login_session.php",
        dataType: "text",
        data: new FormData($("#login_form")[0]),
        processData: false,
        contentType: false,
        success: function (data) {
            if($.trim(data)=="success")
                //put the name on the page
                window.location.assign("cars.html"); //redirect the page to cars.html
            else{
                $("#loading").attr("class","loading_hidden"); //hide the loading icon
                $("#login_feedback").html("Invalid username or password"); //show feedback
            }
        }
    });
}










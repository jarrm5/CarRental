$(document).ready(init);

function init(){
    /* when the user clicks on the logout link, call logout*///
    $("#logout-link").on("click",logout);
    get_login_name();
    //Might need to remove attach_events() if shit hits the fan
    //attach_events();
    get_rented_cars();
    get_returned_cars();
}
//Tab #1
function get_searched_cars(criterion){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "show_searched_cars", search: criterion},
        success: function (data) {
            display(data,"#find-car-template","#search_results");    
        }
    });
}
//Tab #2
function get_rented_cars(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "show_rented_cars"},
        success: function (data) {
            display(data,"#rented-car-template","#rented_cars");    
        }
    });
}
//Tab #3
function get_returned_cars(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "show_returned_cars"},
        success: function (data) {
            display(data,"#returned-car-template","#returned_cars");    
        }
    });
}

function attach_events(){
    
    //Search button
    $("#find-car").on("click",function(){
        var criterion = $("#find-car-input").val();
        get_searched_cars(criterion);
    });
    
    //tab#1 button
    $(".car_rent").on("click",function(){
        var rental_id=$(this).attr("id");
        rent_car(rental_id);
    });
    
    //tab#2 button
    $(".return_car").on("click",function(){
        var rental_id=$(this).attr("data-rental-id");
        return_car(rental_id);
    });
}
//The rent button in Tab #1
function rent_car(id){
      $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "rent_car",id:id},
        success: function (data) {
            if($.trim(data)=="success"){
                alert("The car has been rented successfully");
                get_rented_cars();
                get_returned_cars();
            }
        }
    });
}

//The return button in Tab #2
function return_car(id){
      $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "return_car",id:id},
        success: function (data) {
            if($.trim(data)=="success"){
                alert("The car has been returned successfully");
                get_rented_cars();
                get_returned_cars();
            }
        }
    });
}

function get_login_name(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "login_name"},
        success: function (data) {
            $("#username").html(data);  
        }
    });
}

function logout() {
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "logout"},
        success: function (data) {
            if ($.trim(data)=="success") {
                window.location.assign("index.html");
            }
        }
    });
}
function display(data,template,dest){
    var info_template=$(template).html();
    var html_maker=new htmlMaker(info_template);
    var html=html_maker.getHTML(data);
    $(dest).html(html);
    attach_events();
}

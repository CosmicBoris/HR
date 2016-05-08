/**
 * Created by boris on 08.03.2016.
 */
var mouseStartX, mouseEndX, nInterval;
$(document).ready(function()
{
    $(document).keyup(function(event){
        if(event.which=='37'){
            $('.innerWrap').removeClass('innerShift');
        } else if (event.which=='39'){
            $('.innerWrap').addClass('innerShift');
        } else if(event.which=='27') { // esc keyboard button
            
        } else if(event.which=='13'){}
    });

    $("#btn_menu_trigger").on('click', function() {
        $("#nav-icon3").toggleClass("open");
        $("#menu").toggleClass("open");
    });

    $( ".topLeftCenter" ).on( "mouseup", MouseMove).on( "mousedown", function(event){
        mouseStartX = event.clientX;
    });
    //send login | close popup
    $(".popupbox").on('click', function(event) {
        if( $(event.target).is('.popupbox-close') || $(event.target).is('.popupbox'))
        {
            event.preventDefault();
            if($(event.target).attr('data-attr') != 'glued') {
                $(this).removeClass('open');
            }
        } else if( $(event.target).is('#btnLogin') ) {
            Login();
        }
    });
    $("#menu").on('click', function(event) {
        if(event.target != this) {
            if( $(event.target).is('#logOut')) {
                event.preventDefault();
                deleteAllCookies();
            }
            $("#menu li").removeClass("topCurrent");
            GetAnswer( $(event.target).parent().attr('data-action'), "LContainer");
            $(event.target).parent().addClass("topCurrent");
        }
        $("#menu").removeClass("open");
        $("#nav-icon3").removeClass("open");
    });
});

function LoadINFO(response)
{
    if (!response) {
        alert("Request error!");
    } else if (response.auth == 0) {
        $("#pblogin").addClass("open");
    } else {
        var fh = parseInt(response.freeHeap);
        if( response.freeHeap <= 1024) {
            $("#heap_size").text(fh.toString() + "bytes");
        } else {
            fh /= 1024;
            $("#heap_size").text(fh.toFixed(2).toString() + "Kb");
        }
    }
}

function Login()
{
    if(!$("#pblogin").hasClass('open') || $(".cd-buttons").hasClass('inactive')) {return;}
    var f = document.getElementById("FL");
    $.ajax({
        method: "POST",
        url: $('#FL').attr('action'),
        data: {email: f.elements["email"].value,
            password: CryptoJS.MD5(f.elements["password"].value).toString()}
        })
        .done(function( data ) {
            if(data.error) {
                $(".popupbox-container p").text(data.error);
            } else if(data.id) {
                $('#pblogin').removeClass('open');
                window.location.assign("workspace");
            }
    });
}

function MouseMove(event)
{
    mouseEndX = event.clientX;
    console.log("Mouse down "+mouseStartX+" Mouse up "+mouseEndX);
    if(mouseEndX + 80 < mouseStartX){
        $('.innerWrap').addClass( "innerShift" );
    } else if(mouseStartX < mouseEndX + 80 ){
        $('.innerWrap').removeClass( "innerShift" );
    }
}

function GetAnswer(url, callBackElementId)
{
    var xmlhttp, element = document.getElementById(callBackElementId);
    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();                      //  новые браузеры
    } else {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...
    }
    xmlhttp.onreadystatechange = function()
    {
        if (xmlhttp.readyState == 4) {
            loadingScreen(false);
            if( xmlhttp.status == 200){
                element.innerHTML = xmlhttp.responseText;
                $(element).find("script").each(function(i) {
                    eval($(this).text());
                });
            }
        }
    };
    loadingScreen(true);
    xmlhttp.open("GET", url+'?ajax', true);
    try {
        xmlhttp.send();

        var state = { 'page_id': 1};
        var title = 'hr';

        history.pushState(state, title, url);
    } catch (error){
        GetJsonErrorHandle(error);
    }
}

function GetJsonResponse(address, callbackFunction)
{
    var req;
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();                      //  новые браузеры
    } else {
        req = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...
    }
    req.onreadystatechange = function()
    {
        if (req.readyState == 4 && req.status == 200) {
            loadingScreen(false);
            callbackFunction(JSON.parse(req.responseText));
        } else if (req.status == 404) {
            loadingScreen(false);
            callbackFunction(false);
        }
    };
    req.onerror = GetJsonErrorHandle;
    req.open("GET", address, true);
    loadingScreen(true);
    try {
        req.send();
    } catch (error){
        GetJsonErrorHandle(error);
    }
}

function GetJsonErrorHandle(obj)
{
    loadingScreen(false);
    alert("Request fail"+ obj.message);
}

function loadingScreen(nState)
{
    clearTimeout(nInterval);
    if(nState) {
        nInterval = setTimeout(function(){
            //AddClass("fsc", "overlay-open");
            $(".overlay").toggleClass("on", nState);
        }, 500);
    }else{
        $(".overlay").toggleClass("on", nState);
        //RemoveClass("fsc", "overlay-open");
    }
}

function AddClass(elID, cName)
{
    var element = typeof elID === 'object' ? elID : document.getElementById(elID);
    if(element.className.indexOf(cName) >= 0) return;
    element.className += ' '+cName;
}

function RemoveClass(elID, cName)
{
    var element = typeof elID === 'object' ? elID : document.getElementById(elID);
    element.className = element.className.replace(cName, '');
}

function deleteAllCookies()
{
    var cookies = document.cookie.split(";");
    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i];
        var eqPos = cookie.indexOf("=");
        var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = name + "=;expires=Thu, 01 Jan 2000 00:00:00 GMT";
    }
    $.ajax({
            method: "POST",
            url: $('#FL').attr('action'),
            data: { LOGOUT: "1"}
        })
        .done(function( data ) {
            if(data.auth == 0) {
                $("#pblogin").addClass("open");
            }
        });
}
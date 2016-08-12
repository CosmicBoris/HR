/**
 * Created by boris on 08.03.2016.
 */
var mouseStartX, mouseEndX, nInterval, currentRow, url, id, searchTimer;
$(document).ready(function()
{
    InitSlimScroll(".scroll");

    $(document)
        .keyup(function(event){
        if(event.which=='27') { // esc keyboard button
            $("#new_entry").removeClass('open');
            $("#edit_entry").removeClass('open');
        } else if(event.which=='13'){
            
        }
    })
        .on('click', '.pageAct', function(e){
        if($("#searchbox").length && $("#searchbox").val().length > 2) {
            JsonSearchResult(this);
        } else {
            GetAnswer(this.getAttribute('data-action'), 'LContainer');
        }
    })
        .on('click', ".popupbox", function(event) {  //  send login | close popup

        if( $(event.target).is('.popupbox-close') || $(event.target).is('.popupbox') || $(event.target).is('#pub_no'))
        {
            event.preventDefault();
            if($(event.target).attr('data-attr') != 'glued') {
                $(this).removeClass('open');
            }
        } else if ( $(event.target).is('#btnLogin')) {
            Login();
        }
    })
        .on('click', "#plus_one",function() {
            $("#new_entry").find('h5').text("");
            $("#new_entry").find('form').find("input[type=text], textarea").val("");
            $("#new_entry").addClass('open');
    })
        .on('click', "#btn_edit",function() {
            $("#edit_entry").addClass('open');
        })
        .on('click', "#new_entry", function(evn) {
            if(evn.target == this) {
                $(this).removeClass('open');
                var inputs = $("input[data-role='tagsinput']");
                if(inputs.length > 0)
                    inputs.tagsinput('removeAll');
            }
    })
        .on('click', "#edit_entry", function(evn) {
            if(evn.target == this) {
                $(this).removeClass('open');
            }
        })
        .on('click', "#btnAdd", function() {
        $.ajax({
            type: 'POST',
            url: $('#form_new_entry').attr('action'),
            data: $('#form_new_entry').serialize()
        }).done( function(data )
        {
            if(data.success == 1) {
                $("#new_entry").removeClass('open');
                $('.badge.badge-primary').text(data.vCount);
                $(".table>tbody").html(data.table);
                $(".pull-right>.pagination").detach();
                $(".table-responsive").after(data.pagination);
            } else if (!data.success) {
                $("#new_entry").find('h5').text(data.warning);
            }
        }).fail( function()
        {
            alert("error");
        });
    })
        .on('click', "#btnSaveCan", function(){
            var formObj = $('#form_edit_entry');
            var formElements = document.forms['editCan'].elements;

            $.ajax({
                type: 'POST',
                url: $(formObj).attr('action'),
                data: $(formObj).serialize()
            }).done( function(data ) {
                if(data.success == 1) {
                    $('td[data-name="name"]').html(formElements.fullname.value);
                    $('td[data-name="phone"]').html(formElements.phone.value);
                    $('a[data-name="email"]').attr('href', "mailto:"+formElements.email.value).html(formElements.email.value);
                    $('td[data-name="age"]').html("AGE: "+formElements.age.value);
                    $('td[data-name="sex"]').html("GENDER: " + (formElements.sex.value == 1 ? "Male" : "Female"));
                    $('a[data-name="profile"]').attr('href', formElements.profile.value);
                    $("#target_tag").tagsinput('removeAll');
                    $("#target_tag").tagsinput('add', $("#source_tag").val());

                    $("#edit_entry").removeClass('open');
                } else if (!data.success) {
                    $("#edit_entry").find('h5').text(data.warning);
                }
            }).fail( function() {
                alert("error");
            });
    })
        // DELETE button on table row
        .on('click', 'table .btn[data-action="delete"]', function(e){
        e.preventDefault();
        id = this.id;
        currentRow = $(this).closest("tr");
        url = $(currentRow).closest("table").attr("data-del-ref");
        var message = $(currentRow).children('td:nth-child(2)').text();
        $('.popupbox>div>p').text("Delete \""+message+"\" ?");
        $('.popupbox').addClass('open');
    })
        .on('click', 'table .btn[data-action="info"]', function(e){
            var rel = $(this).closest("table").attr("data-info-ref") + "?id="+ this.id;
            GetAnswerCallback(rel, ShowProfile);
        })
        .on('click', "#goBack", function(){
            $(".slide_side > div:nth-child(2)").remove();
            $("#LContainer").removeClass('minimized');
            window.history.back();
        })
        .on('click', "#pub_ok", DeleteRow)
        .on('click', "#cross", function(evt){
            evt.preventDefault();
            $("#searchbox").val("");
            $(this).removeClass('vis');
        })
        .on("keyup", "#searchbox", function(e){
            //clearTimeout(searchTimer);
                switch(e.which)
                {
                    case 16:
                    case 17:
                    case 18:
                    return;
                }
            if($(this).val().length > 2) {
                $("#cross").addClass("vis");
                /*setTimeout(function(){
                    JsonSearchResult(this);
                }, 700);*/
            } else if ($(this).val().length == 0) {
                $("#cross").removeClass("vis");
                //GetAnswer($(this).attr('data-action'), "wrap");
            }
    });

    $("#btn_menu_trigger").on('click', function() {
        $("#nav-icon3").toggleClass("open");
        $("#menu").toggleClass("open");
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

function Login()
{
    if(!$("#pblogin").hasClass('open') || $(".lg-buttons").hasClass('inactive')) {return;}
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

function ShowProfile(data)
{
    $("#LContainer").addClass("minimized").after(data);
    InitSlimScroll(".scroll");
}

function InitSlimScroll(selector)
{
    $(selector).each(function()
    {
        if ($(this).attr("data-initialized")) {
            return;
        }

        var height;
        if ($(this).attr("data-height")) {
            height = $(this).attr("data-height");
        } else {
            height = $(this).css('height');
        }

        $(this).slimScroll({
            allowPageScroll: true,
            size: '5px',
            color: ($(this).attr("data-handle-color") ? $(this).attr("data-handle-color") : '#bbb'),
            wrapperClass: ($(this).attr("data-wrapper-class") ? $(this).attr("data-wrapper-class") : 'slimScrollDiv'),
            railColor: ($(this).attr("data-rail-color") ? $(this).attr("data-rail-color") : '#eaeaea'),
            height: height,
            alwaysVisible: ($(this).attr("data-always-visible") == "1" ? true : false),
            railVisible: ($(this).attr("data-rail-visible") == "1" ? true : false),
            disableFadeOut: true
        });

        $(this).attr("data-initialized", "1");
    });
}

function InitTagsInput()
{
    $('[data-role="tagsinput"]').each(function()
    {
        if ($(this).attr("data-initialized")) {
            return;
        }

        $(this).tagsinput({
            maxTags: 20
        });

        $(this).attr("data-initialized", "1");
    });
}

function DeleteRow()
{
    $.ajax({
        dataType: "json",
        url: url,
        data: {id: id}
    }).done(function(data){
        if(data.success == 1) {
            $("#delBox").removeClass("open");
            currentRow.fadeOut(500, function(){currentRow.remove();});
            $(".pull-right>.pagination").detach();
            $(".table-responsive").after(data.pagination);
            setTimeout( function() {
                $('.badge.badge-primary').text(data.vCount);
                $(".table>tbody").html(data.table);
            }, 800);
        }
    }).fail(function() {
        alert("error");
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

    xmlhttp.open("GET", url.indexOf('?') !== -1 ? url+'&ajax' : url+'?ajax', true);
    try {
        xmlhttp.send();

        history.pushState({'page_id': 1}, 'hr', url);
    } catch (error){
        GetJsonErrorHandle(error);
    }
}
function GetAnswerCallback(url, callbackFunction)
{
    var req;
    if (window.XMLHttpRequest)
        req = new XMLHttpRequest();                      //  новые браузеры
    else
        req = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...

    req.timeout = 5000;
    req.onreadystatechange = function(){
        if (req.readyState == 4)
            loadingScreen(false);
    };
    req.addEventListener('error', GetJsonErrorHandle);
    req.addEventListener('loadstart', function (evt){
        loadingScreen(true);
    });
    req.addEventListener('load', function(evt){
        if(this.status == 200)
            callbackFunction(req.responseText);
    });
    req.open("GET", url.indexOf('?') !== -1 ? url+'&ajax' : url+'?ajax', true);
    req.send();

    history.pushState({'page_id': 1}, 'hr', url);
}

function PostForm(id_str, callbackFunction)
{
    var form = document.getElementById(id_str);
    var req;
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();                      //  новые браузеры
    } else {
        req = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...
    }

    req.responseType = 'JSON';
    req.timeout = 5000;
    req.addEventListener('load', function(evt){
        if(this.status == 200){
            callbackFunction(JSON.parse(req.response));
        }
    });
    req.addEventListener('error', GetJsonErrorHandle);
    req.addEventListener('loadstart', function (evt){
        loadingScreen(true);
    });

    req.onreadystatechange = function(){
        if (req.readyState == 4)
            loadingScreen(false);
    };
    req.open("POST", form.getAttribute('action'), true);
    req.send( serialize(form) );
}

function GetJsonResponse(url, callbackFunction)
{
    var req;
    if (window.XMLHttpRequest)
        req = new XMLHttpRequest();                      //  новые браузеры
    else
        req = new ActiveXObject("Microsoft.XMLHTTP");    //  древние IE 5...

    req.responseType = 'JSON';
    req.timeout = 5000;
    req.onreadystatechange = function(){
        if (req.readyState == 4)
            loadingScreen(false);
    };
    req.addEventListener('error', GetJsonErrorHandle);
    req.addEventListener('loadstart', function (evt){
        loadingScreen(true);
    });
    req.addEventListener('load', function(evt){
        if(this.status == 200)
            callbackFunction(JSON.parse(req.response));
    });
    req.open("GET", url, true);
    req.send();
}

function GetJsonErrorHandle(obj)
{
    loadingScreen(false);
    alert("Request fail"+ obj.message);
}

function DeleteCandidate(e)
{
    $.ajax({
        method: "GET",
        dataType: "json",
        data: {id: e.id, table: 'candidates'},
        url: '/workspace/Delete',
        timeout: 2000
    }).done(function(data) {
        if(data.success == 1) {

            $("#delBox").removeClass("open");
            var tr = $(e).closest('tr');
            tr.fadeOut(500, function() {
                tr.remove();
            });
            
            setTimeout(function(){
                GetAnswer('/workspace/Candidates', "LContainer")
            }, 800);
        }
    }).fail(function() {
        alert("error");
    });


    $.ajax({
        dataType: "json",
        url: rel,
        data: {id: id}
    }).done(function(data){
        if(data.success == 1) {
            $("#delBox").removeClass("open");
            currentRow.fadeOut(500, function(){currentRow.remove();});
            $(".pull-right>.pagination").detach();
            $(".table-responsive").after(data.pagination);
            setTimeout( function() {
                $('.badge.badge-primary').text(data.vCount);
                $(".table>tbody").html(data.table);
            }, 800);
        }
    }).fail(function() {
        alert("error");
    });
}

function loadingScreen(nState)
{
    clearTimeout(nInterval);
    if(nState) {
        nInterval = setTimeout(function(){
            $(".overlay").toggleClass("on", nState);
        }, 200);
    }else{
        $(".overlay").toggleClass("on", nState);
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
/*for(var key in formData)
 $("#t_info").find("td[data-name*='"+key+"']" ).text(formData[key]);*/
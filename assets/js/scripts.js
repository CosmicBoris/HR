/**
 * Created by boris on 08.03.2016.
 */
var App = function(){
    
}();


var mouseStartX, mouseEndX, nInterval, currentRow, url, id, searchTimer, backUp;
var needReload = false;
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
        .on('click', '.pageAct', function() {

            var sb = $("#searchbox")[0];

        if(sb.value && sb.value.length > 2) {
            GetAnswer(this.getAttribute('data-action')+'?search='+sb.value, 'LContainer');
        } else {
            GetAnswer(this.getAttribute('data-action'), 'LContainer');
        }
    })
        .on('click', ".popupbox", function(event) {  //  send login | close popup
            var etarget = $(event.target);

            if( etarget.is('.popupbox-close') || etarget.is('.popupbox') || etarget.is('#pub_no')) {
                event.preventDefault();
                if(etarget.attr('data-attr') != 'glued')
                    $(this).removeClass('open');

            } else if (etarget.is('#btnLogin')) {
                Login();
            }
        })
        .on('click', "#plus_one",function() {
            var elm = $("#new_entry");
            elm.find('h5').text("");
            elm.find('form').find("input[type=text], textarea").val("");
            elm.addClass('open');
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
        .on('click', "#show_entry", function(evn) {
            if(evn.target == this || $(evn.target).is('.popupbox-close')) {
                $(this).removeClass('open');
            }
        })
        .on('click', "#btnAdd", HandleNewEntry)
        .on('click', "#btnAddEvent", HandleNewEvent)
        .on('click', "#btnSaveCan", function(){
            var formObj = $('#form_edit_entry');
            var formElements = document.forms['editCan'].elements;

            $.ajax({
                type: 'POST',
                url: $(formObj).attr('action'),
                data: $(formObj).serialize()
            }).done( function(data ) {
                if(data.success == 1) {
                    $('[data-name="name"]').html(formElements.fullname.value);
                    $('td[data-name="phone"]').html(formElements.phone.value);
                    $('a[data-name="email"]').attr('href', "mailto:"+formElements.email.value).html(formElements.email.value);
                    $('td[data-name="birthdate"]').html(formElements.birthdate.value + "  /  AGE: "+
                    CalculateAge(formElements.birthdate.value));
                    $('td[data-name="sex"]').html((formElements.sex.value == 1 ? "Male" : "Female"));
                    $('a[data-name="profile"]').attr('href', formElements.profile.value);
                    var ti = $("#target_tag");
                    ti.tagsinput('removeAll');
                    ti.tagsinput('add', $("#source_tag").val());

                    $("#edit_entry").removeClass('open');
                } else if (!data.success) {
                    $("#edit_entry").find('h5').text(data.warning);
                }
            }).fail( function() {
                alert("error");
            });
        })
        .on('click', "#btnSaveVac", function(){
            var formObj = $('#form_edit_entry');
            var formElements = document.forms['editVac'].elements;

            $.ajax({
                type: 'POST',
                url: formObj.attr('action'),
                data: formObj.serialize()
            }).done( function(data ) {
                if(data.success == 1) {
                    $('[data-name="title"]').html(formElements.title.value);
                    $('td[data-name="description"]').html(formElements.description.value);
                    $('td[data-name="state"]').html(formElements.state.checked == 1 ? "Opened" : "Closed");

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
        url = currentRow.closest("table").attr("data-del-ref");
        var message = currentRow.children('td:nth-child(2)').text();
        $('.popupbox>div>p').text("Delete \""+message+"\" ?");
        $('.popupbox').addClass('open');
    })
        .on('click', 'table .btn[data-action="info"]', function(e){
            id = this.id;
            var rel = $(this).closest("table").attr("data-info-ref") + "?id="+ this.id;
            GetAnswerCallback(rel, ShowProfile, true);
        })
        .on('click', "#goBack", function(){
            /*$(".slide_side > div:nth-child(2)").remove();
            $("#LContainer").removeClass('minimized');*/

            var container = $("#LContainer");
            container.addClass('minimized');
            container.html(backUp);
            container.removeClass('minimized');

            window.history.back();
        })
        .on('click', "#pub_ok", DeleteRow)
        .on('click', "#cross", function(evt){
            evt.preventDefault();
            $("#searchbox").val("").trigger("keyup");
            $(this).removeClass('vis');
        })
        .on("keyup ", "#searchbox", function(e) {
            switch(e.which) {
                case 16:
                case 17:
                case 18:
                    return false;
            }
            if(this.value.length > 2) {
                $("#cross").addClass("vis");
                var uri = $(this).attr('data-action')+'?search_str='+this.value;
                GetAnswerCallback(uri, SearchResult);
                needReload = true;
            } else if (this.value.length == 0) {
                $("#cross").removeClass("vis");
                if(needReload) {
                    GetAnswer($(this).attr('data-action'), "LContainer");
                    needReload = false;
                }
            }
        })
        .on('click', "#profile_photo", function(){
            var pop = $("#fdOpen");
            pop.addClass("showUp");
            setTimeout(function(){
                pop.removeClass("showUp");
            }, 5000);
        })
        .on('click', "#fdOpen", function(){
            $("#photoUpload").click();
        })
        .on('change', "#photoUpload", HandlePhotoUpload);

    $("#btn_menu_trigger").on('click', function() {
        $("#nav-icon3").toggleClass("open");
        $("#menu").toggleClass("open");
    });

    $("#menu").on('click', function(event) {
        if(event.target != this) {
            if( $(event.target).text() == "LogOut") {
                event.preventDefault();
                return LogOut();
            }
            $(this).find("li").removeClass("topCurrent");
            GetAnswer( $(event.target).parent().attr('data-action'), "LContainer");
            $(event.target).parent().addClass("topCurrent");
        }
        RemoveClass(this, "open");
        $("#nav-icon3").removeClass("open");
    });
});

/*source http://stackoverflow.com/questions/10008050/get-age-from-birthdate*/
function CalculateAge(dateString)
{
    var today = new Date();
    var birthDate = new Date(dateString);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}
function HandlePhotoUpload(e)
{
    var file = e.target.files[0];
    if (file) {
        var cv = document.createElement("canvas");
        cv.width = 300;
        cv.height = 300;
        var cvc = cv.getContext("2d");
        var img = new Image();
        img.onload = function(e){

            var percentW = cv.width / e.target.width,
                percentH = cv.height / e.target.height;
            var percent = percentW > percentH ? percentW : percentH;

            var newWidth = Math.floor(percent * e.target.width),
                newHeight = Math.floor(percent * e.target.height);

            var c = document.createElement("canvas");
            c.width = newWidth;
            c.height = newHeight;
            var cc = c.getContext("2d");
            cc.drawImage(e.target, 0, 0, newWidth, newHeight);
            var sx = Math.floor((newWidth - cv.width) / 2),
                sy = Math.floor((newHeight - cv.height) / 2);
            cvc.drawImage(c, sx, sy, cv.width, cv.height, 0, 0, cv.width, cv.height);
            var imgData = cv.toDataURL("image/jpeg");
            $("#profile_photo").find("img").attr('src', imgData);

            $.ajax({
                type: 'POST',
                url: '/Workspace/AddPhoto',
                data: {
                    id: $("h2.info").attr("data-id"),
                    photo: imgData
                }
            }).done( function(data ){
                if(data.success == 1) {}
                else if (!data.success) {}
            }).fail( function(){
                alert("error");
            });
        };
        var reader = new FileReader();
        reader.onload = function(event){
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
}
function HandleNewEntry(e)
{
    var formObj = $('#form_new_entry');

    $.ajax({
        type: 'POST',
        url: formObj.attr('action'),
        data: formObj.serialize()
    }).done( function(data ){
        if(data.success == 1) {
            $("#new_entry").removeClass('open');
            $('.badge.badge-primary').text(data.vCount);
            $(".table>tbody").html(data.table);
            $(".pull-right>.pagination").detach();
            $(".table-responsive").after(data.pagination);
        } else if (!data.success) {
            $("#new_entry").find('h5').text(data.warning);
        }
    }).fail( function() {
        alert("error");
    });
}
function HandleNewEvent()
{
    var formObj = $('#form_new_entry');
    $.ajax({
        type: 'POST',
        url: formObj.attr('action'),
        data: formObj.serialize()
    }).done( function(data ){
        if(data.success == 1) {
            $("#new_entry").removeClass('open');
            $('#fullcalendar').fullCalendar('refetchEvents');
            if(formObj.find('input[name="event_type"]').val() == "event-interview"){
                var els = $("#interCount");
                els.text(parseInt(els.text()) + 1);
            }
        } else if (!data.success) {
            $("#new_entry").find('h5').text(data.warning);
        }
    }).fail( function() {
        alert("error");
    });
}
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
    var elem = $("#LContainer");
    backUp = elem.html();
    /*elem.addClass("minimized").html(data);*/
    elem.addClass("minimized");
    setTimeout(function(){
        elem.html(data);
        InitSlimScroll(".scroll");
        elem.removeClass('minimized');
    },300);
}

function InitSlimScroll(selector)
{
    $(selector).each(function()
    {
        if ($(this).attr("data-initialized"))
            return;

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
function InitDateTime(selector)
{
    $(selector).each(function()
    {
        if ($(this).attr("data-init"))
            return;

        $(this).datetimepicker({
            format: "dd-MM-yyyy hh:ii",
            autoclose: true,
            todayBtn: true,
            minuteStep: 10
        });

        $(this).attr("data-init", "1");
    });
}

function InitTagsInput()
{
    $('[data-role="tagsinput"]').each(function(){
        if ($(this).attr("data-initialized"))
            return;

        $(this).tagsinput({maxTags: 20});
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
        alert("Delete Row error");
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
                $(element).find("script").each(function() {
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
function GetAnswerCallback(url, callbackFunction, pushState)
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

    if(pushState)
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

function SearchResult(data)
{
    data = JSON.parse(data);
    if(data.success) {
        $(".pull-right>.pagination").detach();
        $(".table-responsive").after(data['pagination']);
        $(".table>tbody").html(data['table']);
        $(".badge-primary").html(data['vCount']);
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

function LogOut()
{
    deleteAllCookies();
    $.ajax({
        method: "POST",
        url: "/Logout",
        data: { LOGOUT: "1"}
    })
    .done(function( data ) {
        window.location.reload(true);
    });
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
}
/*for(var key in formData)
 $("#t_info").find("td[data-name*='"+key+"']" ).text(formData[key]);*/


var Calendar = function()
{
    var _calendar, _paper_info, cWidth = 0;
    var NotifyServer = function(data, undoFn)
    {
        $.ajax({
            type: 'POST',
            url: '/workspace/UpdateEvent',
            data: data
        }).done( function(responce){
            if(responce.success == 1) {

            } else if (!responce.success) {
                undoFn();
                alert("Not s");
            }
        }).fail( function() {
            undoFn();
            alert("Server error, can`t save changes");
        });
    };
    var openInfo = function()
    {
        _paper_info.addClass('open');
    };
    var eventAdditionalData = function(data)
    {
        var html = "";
        html += '<hr><h3>On vacancy:</h3><table class="table_info"><tbody><tr><td class="pad_l _hi">Title:</td><td colspan="3">'
            +data.vacancy.title
            +'</td></tr><tr><td class="pad_l _hi">Created:</td><td>'
            +data.vacancy.date_added
            +'</td><td class="pad_l _hi">State:</td><td>'
            +(data.vacancy.state == 1 ? "Opened":"Closed")
            +'</td></tr><tr><td class="pad_l _hi">Description:</td>'
            +'<td class="text-center" rowspan="3" colspan="3">'
            +data.vacancy.description
            +'</td></tr><tr></tr><tr></tr></tbody></table>'
            +'<hr><h3>With candidate:</h3>'
            +'<table class="table_info"><tbody><tr><td id="photo_container" rowspan="4"><div id="profile_photo">'
            +'<img src="'
            +(data.candidate.photo ? data.candidate.photo : "/assets/svg/no_photo.svg")
            +'" height="128" width="128"></div></td><td class="pad_l _hi">NAME:</td><td>'
            +data.candidate.fullname+'</td></tr><tr><td class="pad_l _hi">PHONE:</td><td>'
            +data.candidate.phone+'</td></tr><tr><td class="pad_l _hi" >EMAIL:</td>'
            +'<td><a href="mailto:'+data.candidate.email+'">'+data.candidate.email+'</a></td>'
            +'</tr><tr><td class="pad_l _hi">GENDER:</td><td>'+(data.candidate.sex ? "Male" : "Female")
            +'</td></tr><tr><td class="text-center"><a data-name="profile" href="'
            +(data.candidate.profile ? data.candidate.profile : "")
            +'" target="_blank">Web profile</a></td><td class="pad_l _hi">Birth date:</td><td>'
            +data.candidate.birthdate
            +'</td></tr></tbody></table>';


        $("#vacancy_info").html(html);

        openInfo();
    };

    return {
        init: function() {
            Calendar.initCalendar();
        },
        initCalendar: function() {
            if (!jQuery().fullCalendar) {
                return;
            }
            _calendar = $('#fullcalendar');
            _paper_info = $("#show_entry");

            if (_calendar.parents(".panel-body").width() <= 720)
                _calendar.addClass("mobile");
            else
                _calendar.removeClass("mobile");

            var initDrag = function(el)
            {
                var eventObject = {
                    title: $.trim($(this).text()), // use the element's text as the event title
                    stick: true // maintain when user navigates (see docs on the renderEvent method)
                };
                el.data('event', eventObject);
                el.draggable({
                    zIndex: 999,
                    revert: true,
                    revertDuration: 0
                });
            };

            var addEvent = function(title) {
                title = title.length === 0 ? "Untitled Event" : title;
                var html = $('<div class="external-event" data-class="event-meeting">' + title + '</div>');
                $('#event_box').append(html);
                initDrag(html);
            };

            $('#event_add').off('click').click(function() {
                var title = $('#event_title').val();
                addEvent(title);
            });

            $( window ).resize(function(event) {
                if(event.target.innerWidth > cWidth + 10 || event.target.innerWidth < cWidth - 10){
                    var ar =  Math.floor(event.target.innerWidth / 940);
                    ar = ar > 1 ? ar : 1;
                    _calendar.fullCalendar('option', 'aspectRatio', ar);
                    cWidth = event.target.innerWidth;
                }
            });

            _calendar.fullCalendar({
                events: '/workspace/Feed',
                firstDay: 1,
                timeFormat: 'H(:mm)',
                slotLabelFormat: [
                    'ddd D/M',
                    'H:mm'
                ],
                minTime: "06:00:00",
                maxTime: "21:00:00",
                snapDuration:1,
                fixedWeekCount: false,
                aspectRatio: 2.1,
                header: {
                    left: 'prev,today,next',
                    center: 'title',
                    right: 'month,basicWeek,agendaDay'
                },
                editable: true,
                droppable: true,
                eventClick: function(event, jsEvent, view)
                {
                    var str = "";
                    for (var key in event) {
                        var value = event[key];
                        str += key+': ' + value+'\r\n';
                    }

                    var tinfo = $("#t_info");
                    $('h3[data-name="event_type"]').html(event.event_type);
                    tinfo.find('[data-name="title"]').html(event.title);
                    tinfo.find('[data-name="created"]').html(event.created);
                    tinfo.find('[data-name="start"]').html(event.start.format("YYYY-MM-DD HH:mm"));
                    tinfo.find('[data-name="end"]').html(event.end.format("YYYY-MM-DD HH:mm"));
                    tinfo.find('[data-name="description"]').html(event.description);

                    if(event.event_type.length && event.event_type == 'event-interview')
                    {
                        var url = '/workspace/VacancyCandidateData?candidate_id=' +event.candidate_id
                            + '&vacancy_id='+event.vacancy_id;
                        GetJsonResponse(url, eventAdditionalData);
                    }
                    else
                    {
                        openInfo();
                    }

                },
                drop: function(date, allDay)
                {
                    var $this = $(this),
                        eventObject = {
                            title: $this.text(),
                            start: date,
                            allDay: allDay,
                            className: $(this).attr("data-class")
                        };

                    _calendar.fullCalendar('renderEvent', eventObject, true);

                    $this.remove();
                },
                eventDrop: function(event, delta, revertFunc)
                {
                    var eventData = {
                        id: event.id,
                        start: event.start.format("YYYY-MM-DD HH:mm:ss")
                    };
                    if('end' in event && event.end !== null)
                        eventData.end = event.end.format("YYYY-MM-DD HH:mm:ss");

                    NotifyServer(eventData, revertFunc);
                },
                eventResize: function(event, delta, revertFunc)
                {
                    var eventData = {
                        id: event.id,
                        end: event.end.format("YYYY-MM-DD HH:mm:ss")
                    };
                    NotifyServer(eventData, revertFunc);
                }
            });
        }
    };
}();
/*jshint esversion: 6 */

class Diagram {
    constructor() {
        this.id = null;
        this.diagram_name = null;
        this.markdown = null;
        this.author = null;
        this.link = null;
        this.updated_at = null;
    }
}

class Task {
    constructor() {
        this.project_id = '';
        this.id = '';
        this.title = '';
        this.description = '';
        this.assignee = '';
        this.verifier = '';
        this.task_category = '';
        this.task_column = '';
        this.comments = null; // [{'comment':'', timestamp: '', by:''}]
        this.attachments = null;
    }
}

function initializeDataTable(tableId){
      
    return $('#'+tableId).DataTable({
          "responsive": true,
          "autoWidth": false,
          "stateSave": true,
          "pagingType": "full_numbers",
          "paging": true,
          "lengthMenu": [10, 25, 50, 75, 100],
    });
}

function makeRequest(url){
    return new Promise((resolve, reject) => {
        $.ajax({
            type: 'GET',
            url: url,
            success: function (response) {    
                try{
                    response = JSON.parse(response);
                }catch(e){
                    showPopUp('Error', 'Session timed out! Login Again.');
                    return false;
                }   
                resolve(response);
            },
            error: function (err) {
                reject(err);
            }
        });
    })
    
}

function makePOSTRequest(url, data, fileData=false){
    if(!fileData){
        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                success: function (response) {  
                    try{
                        response = JSON.parse(response);
                    }catch(e){
                        showPopUp('Error', 'Session timed out! Login Again.');
                        return false;
                    }     
                    resolve(response);
                },
                error: function (err) {
                    reject(err);
                }
            });
        });
    }else{
        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                contentType: false,
                cache: false,
                processData:false,
                success: function (response) {  
                    try{
                        response = JSON.parse(response);
                    }catch(e){
                        showPopUp('Error', 'Session timed out! Login Again.');
                        return false;
                    }     
                    resolve(response);
                },
                error: function (err) {
                    reject(err);
                }
            });
        });
    }

}

function showPopUp(title, message){
    bootbox.alert({
            title: title, 
            message: message,
            centerVertical: true,
            backdrop: true
        });
}

function formatDate(utcDate, useOffset = true) {
    let utc = new Date(utcDate);
    var date;

    if(useOffset){
       date = new Date(utc.getTime() + ( 5.5 * 60 * 60 * 1000 ));
    }else{
        date = utcDate;
    }
    
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    month = month < 10 ? '0' + month : '' + month;
    var day = date.getDate();
    day = day < 10 ? '0' + day : '' + day;
    var hours = date.getHours();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    hours = hours < 10 ? '0' + hours : '' + hours;
    var minutes = date.getMinutes();
    minutes = minutes < 10 ? '0'+minutes : minutes;
    
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return year+"-"+month+"-"+day+" "+strTime;
}

function getCurrentDateForDB(){
    let date = new Date(new Date().toUTCString().substr(0, 25));
   
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    month = month < 10 ? '0' + month : '' + month;
    var day = date.getDate();
    day = day < 10 ? '0' + day : '' + day;
    var hours = date.getHours();
    hours = hours < 10 ? '0'+hours : hours;
    var minutes = date.getMinutes();
    minutes = minutes < 10 ? '0'+minutes : minutes;
    var seconds = date.getSeconds();
    seconds = seconds < 10 ? '0'+seconds : seconds;
    
    var strTime = hours + ':' + minutes + ':' + seconds;
    return year+"-"+month+"-"+day+" "+strTime;

}

function showFloatingAlert(message , bgClass = "bg-success"){
    $(".floating-alert").removeClass("bg-success bg-danger bg-warning");
    $(".floating-alert").addClass(bgClass);
    let icon = "";

    if(bgClass == "bg-success"){
        icon = '<i style="font-size:18px;margin-right: 4px;" class="fa fa-check-circle" aria-hidden="true"></i> ';
    }else if(bgClass == "bg-warning"){
        icon = '<i style="font-size:18px;margin-right: 4px;" class="fa fa-info-circle" aria-hidden="true"></i> ';
    }else{
        icon = '<i style="font-size:18px;margin-right: 4px;" class="fa fa-exclamation-triangle" aria-hidden="true"></i> ';
    }

    $(".floating-alert").html(icon+message);
    
    $('.floating-alert').show('slide', {direction: 'right'}, 1000);

    window.setTimeout(function() {
        $('.floating-alert').hide('slide', {direction: 'right'}, 2000);
    
    }, 3000);
}

function copyToClipboard(link, location = "body") {    
    $(`${location}`).append(`<input value="${link}" id="copyToClipboard" style="opacity:0">`);
    var input = $("#copyToClipboard");

    input.focus();
    input.select();
    document.execCommand('copy');
    input.remove();

    showFloatingAlert("Success: Link Copied!");
}

function previewImage(name, link){
    var protocol = location.protocol;
    var slashes = protocol.concat("//");
    var host = slashes.concat(window.location.hostname);

    bootbox.dialog({
        title: `${name}`,
        message: `<div class="text-center">
                    <img style="max-width:100%" src='${link}' />
                    </div> `,
        size: 'large',
        buttons: {
            ok: {
                label: "OK",
                className: 'btn-primary'
            }
        }
        
    });
    $(".modal-footer").append(`<div style='left: 10px;position: absolute;'>
                                    <button class="btn btn-orange ml-2" onclick="copyToClipboard('${host}${link}', '.modal-body')" 
                                        data-toggle="popover" data-placement="left" data-content="Copy to clipboard" >
                                        <i class="fas fa-clipboard"></i>
                                    </button>
                                    <a download href="${link}"  
                                        data-toggle="popover" data-placement="right" data-content="Download Image"
                                        class="download-link btn btn-purple ml-2" >
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>`);
    $('[data-toggle="popover"]').popover({trigger: "hover" });
}


function attachmentSlider(title, carouselHtml){
    
    let carouselIndicators = carouselHtml[0];
    let carouselItems = carouselHtml[1];

    bootbox.dialog({
        title: `${title}`,
        message: `
        <div id="carouselExampleIndicators" class="carousel slide  carousel-fade" data-ride="carousel">
            <ol class="carousel-indicators">
                ${carouselIndicators}
            </ol>
            <div class="carousel-inner">
                ${carouselItems}
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
        `,
        size: 'large',
        
    });
   
}

function getHTMLtable(data, dataInfo){
    let tbody = "";

    if(data.length){
        data.forEach((record, index)=> {

            let columns = "";
            dataInfo.requiredFields.forEach(value => {
                let storedVal = record[value];
                if(dataInfo.dateFields.includes(value)){
                    storedVal = formatDate(record[value]);
                }

                columns += `<td>${storedVal}</td>`;
            });

            let actionButtons = "";
            dataInfo.action.forEach(actionObject => {
                
                let clickParams = "";
                actionObject.clickParams.forEach( (param, i) => {
                    clickParams += `'${record[param]}'`;
                    if(i < (actionObject.clickParams.length-1)){
                        clickParams += ",";
                    }
                });

                let includeButton = true;
                if ('condition' in actionObject){
                    if(actionObject.condition.with != record[actionObject.condition.on]){
                        includeButton = false;
                    }
                }

                if(includeButton){
                    const button = `<button title="${actionObject.title}" class="ml-2 ${actionObject.buttonClass}" onclick="${actionObject.clickTrigger}(${clickParams})" >
                                    <i class="${actionObject.iconClass}"></i>
                                </button>`;
                    actionButtons += button;
                }
                
                
            });

            columns += `<td class="text-center">${actionButtons}</td>`;

            let row = `<tr id="${record[dataInfo.rowId]}"><td>${++index}</td>${columns}</tr>`;
            tbody += row;
        });

    }else{
        let row = `<tr><td valign="top" colspan="${(dataInfo.requiredFields.length+2)}" class="dataTables_empty">No data available</td></tr>`;
        tbody += row;
        showFloatingAlert("No data available." , "bg-warning");
    }    

    return tbody;
}

// Review and Documents Shared Code
function  addReviewCommentToUI(reviewId, comment) {
    const commentEdit = (userName != comment.by) ? 'hide' : '';
    let commentHtml = `<li class="list-group-item list-group-item-action " id = "${comment.id}">
                               <div class="w-100 text-right float-right ${commentEdit}">
                                   <button data-toggle="popover" data-placement="bottom" data-content="Edit Comment" type="button"
                                       class="ml-1 btn btn-sm box-shadow-right btn-sm-primary btn-primary" 
                                       onclick="editComment('${comment.id}')">
                                       <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                                   </button>
                                   <button data-toggle="popover" data-placement="bottom" data-content="Delete Comment" type="button"
                                       class="ml-1 btn btn-sm box-shadow-right btn-sm-danger btn-danger"
                                       onclick="deleteComment('${reviewId}', '${comment.id}')">
                                       <i class="fa fa-trash" aria-hidden="true"></i>
                                   </button>
                               </div>
   
                               ${SimpleMDE.prototype.markdown(comment.message)}
                               
                               <footer class="d-flex w-100 justify-content-between">
                                    <div>
                                        <cite class=" mt-3" style="font-size:11px">${formatDate(comment.timestamp)}</cite>
                                    </div>
                                    <div>
                                        <span class="blockquote-footer"><cite>${comment.by}</cite> </span>
                                        
                                    </div>
                               </footer>
                           </li>`;

    $(".commentsList").prepend(commentHtml);
    $('[data-toggle="popover"]').popover({
        trigger: "hover"
    });
}

function showReview() {
    const $codemirror = $('textarea[name="description"]').nextAll('.CodeMirror')[0].CodeMirror;
    if (toggleReviewBox) {
        $(".commentsList").removeClass("withoutReviewBox");
        $(".commentsList").addClass("withReviewBox");
        $(".reviewbox").fadeIn();

        $codemirror.refresh();
        textareaFocus($codemirror);
    } else {
        $codemirror.getDoc().setValue("");
        $(".commentsList").removeClass("withReviewBox");
        $(".commentsList").addClass("withoutReviewBox");
        $(".reviewbox").fadeOut();
        commentEditId = "";

    }

    toggleReviewBox = !toggleReviewBox;
}

function textareaFocus($codemirror){
    $codemirror.focus();
    const lastLine = $codemirror.lastLine();
    $codemirror.setCursor({
            line: lastLine             
    });
}

function getObjectFromArray(objectId, objectArray) {
    var requiredObject, requiredObjectLoc;

    objectArray.some((object, index) => {
        if (object.id == objectId) {
            requiredObject = object;
            requiredObjectLoc = index;
            return true;
        }
    });

    return [requiredObjectLoc, requiredObject];
}

function editComment(commentId) {
    commentEditId = commentId;
    let comment = getObjectFromArray(commentId, reviewComments);

    const $codemirror = $('textarea[name="description"]').nextAll('.CodeMirror')[0].CodeMirror;
    const message = comment[1].message;
    $codemirror.getDoc().setValue(message);

    showReview();
}

function deleteComment(reviewId ,commentId) {

    bootbox.confirm({
        title: 'Delete',
        message: `Are you sure you want to delete ?`,
        buttons: {
            cancel: {
                label: '<i class="fa fa-times"></i> Cancel'
            },
            confirm: {
                label: '<i class="fa fa-check"></i> Confirm'
            }
        },
        callback: function(result) {
            if (result) {
                // const reviewId = $("#reviewId").val();
                let data = {
                    "commentId": commentId,
                    reviewId
                };

                makePOSTRequest('/reviews/deleteComment', data)
                    .then((response) => {
                        if (response.success == "True") {
                            let previousComment = getObjectFromArray(commentId, reviewComments);
                            reviewComments.splice(previousComment[0], 1);
                            $("#" + commentId).fadeOut(800, function() {
                                $(this).remove();
                            });
                            showFloatingAlert(response.message);
                        } else {
                            showPopUp('Error', response.errorMsg);
                        }
                    })
                    .catch((err) => {
                        console.log(err);
                        showPopUp('Error', "An unexpected error occured on server.");
                    })

            } else {
                console.log('Delete Cancelled');
            }
        }
    });


}


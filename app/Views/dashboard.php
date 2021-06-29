<style>
body {
    background-color: rgb(239, 244, 247);
}

.toggle-menu{
    border: 5px solid rgb(239, 244, 247);
}

.breadcrumb {
    background-color: #fff;
}

.breadcrumb-item {
    font-size: 24px;
}

.card {
    box-shadow: 0px 1px 22px -12px #607D8B;
    background-color: #fff;
    padding: 6px 12px;
    border-radius: 8px;
    width: 95%;
    margin-left: auto;
    margin-right: auto;
}

.card:hover {
    box-shadow: 0px 10px 32px -12px #607D8B;
}

.card-body {
    padding: 5px;
}

ul {
    list-style: none;
    counter-reset: my-awesome-counter;
}

ul li>p {
    counter-increment: my-awesome-counter;
}

ul li>p::before {
    content: counter(my-awesome-counter) ". ";
    color: "#6c757d";

}

.sonarProject {
    border: 1px solid #dee2e6;
    padding: 2px 12px;
    border-radius: 8px;
    margin-top: 5px;
}

.sonarProject:hover {
    background: #f8f9fa;
    border: 0.1rem solid #6c757d;
}

.statsHeading {
    padding-left: 30px;
    font-size: 18px;
}

.para {
    font-size: 14px;
    margin: 0px;
}

.small-text {
    font-size: 12px;
}

.build-link-div {
    border: 0.1rem solid #6c757d;
    border-radius: 4px;
    padding: 5px;
    margin-left: 4px;
    margin-right: 4px;
    background: #6c757d;
}

.build-link:hover {
    text-decoration: none;
    font-weight: 600;
}


 .changeListItems:hover{
    border:0.1rem solid #6c757d;
    cursor: default;
}
</style>

<div class="fluid-container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  arr-right" style="margin:0px;">
            <li class="breadcrumb-item text-primary" aria-current="page"> Dashboard </li>
        </ol>
    </nav>
    <main class="pt-3 pb-3">
        <div class="stats-container row justify-content-center"></div>

    </main>
</div>

<script>
$(document).on({
    ajaxStart: function() {
        $("#loading-overlay").show();
    },
    ajaxStop: function() {
        $("#loading-overlay").hide();
    }
});

function getStats() {
    makeRequest("/dashboard/getStats")
        .then(response => {
            if (response.jenkins_stats != null) {
                showJenkinsStats(response.jenkins_stats);
            }

            if (response.sonar_stats != null) {
                showSonarStats(response.sonar_stats);
            }
            $('[data-toggle="popover"]').popover({
                trigger: "hover"
            });
        })
        .catch(err => console.log(err))
}

getStats();

function showJenkinsStats(stats) {
    const jenkinsHtml = populateBuildStats(stats);
    $(".stats-container").append(jenkinsHtml);
}

function populateBuildStats(stats) {
    const changeLog = stats.changeLog;
    let changes = changeLog.length > 0 ? changeLog.length : "No changes";
    changes += (changeLog.length == 1 ? " commit" : (changeLog.length > 1 ? " commits" : ""))

    let changesList = '';
    if (changeLog.length) {
        changesList = `
       <div class="card-body ">
            <ul class="list-group scroll scroll-dark" style="max-height:65vh;">
        `;

        for (let i = 0; i < changeLog.length; i++) {
            changesList += `
                <li   
                    class="list-group-item list-group-item-action changeListItems"   >
                    <p class="para font-weight-light"><i data-toggle="popover" data-content="${formatDate2(changeLog[i]["timestamp"])}" data-placement="left"
                    title="${changeLog[i]["authorEmail"]}" class="fa fa-info-circle text-secondary" aria-hidden="true" style="
    float: right;
"></i>${changeLog[i]["msg"]}</p>
                   
                </li>
            `;
        }

        changesList += `</ul></div>`;
    }

    let resultClass = "text-secondary";
    if (stats.result == "SUCCESS") {
        resultClass = "text-success";
    } else if (stats.result == "FAILURE") {
        resultClass = "text-danger";
    } else {
        stats.result = "BUILDING";
    }

    const buildSpecificLinks = getJenkinsLinks(stats.url);

    let buildLinks = "";
    if(buildSpecificLinks){
        buildLinks = `
                    <div class="d-flex justify-content-around build-link-div text-secondary font-weight-light ">
                        <a target="_blank"  href="${buildSpecificLinks.buildLocation}" data-toggle="popover" data-content="Goto build location"  data-placement="top" class="build-link text-light">Build</a> |
                        <a target="_blank"  href="${buildSpecificLinks.liveInstance}"  data-toggle="popover" data-content="Goto deployed instance"  data-placement="top" class="build-link text-light">Live Instance</a> |
                        <a target="_blank"  href="${buildSpecificLinks.automationReport}"  data-toggle="popover" data-content="View automation report" data-placement="top" class="build-link text-light">Automation Report</a> |
                        <a target="_blank"  href="${buildSpecificLinks.unitTestReport}"  data-toggle="popover" data-content="View unit test report" data-placement="top" class="build-link text-light">Unit Test Report</a>
                    </div>
                    `;
    }

    let jenkinsHtml = `
            <div class="col-lg-6 pr-0">
                <label class="statsHeading font-weight-bold">Jenkins Build</label>
                <div class="card">
                    <div class="d-flex justify-content-between pt-2 pl-1 pr-1">
                        <span class="${resultClass} font-weight-bold">${stats.result}</span>
                        <a target="_blank" href="${stats.url}" class="text-secondary font-weight-bold"><span class="">#${stats.id}</span></a>
                        <label class="text-secondary font-weight-light">${changes}</label>
                    </div>

                    ${changesList}

                    ${buildLinks}

                    <div class="d-flex justify-content-between pt-1 small-text">
                        <cite data-toggle="popover" data-content="Build Duration" class="text-secondary font-weight-light">${secondsToDuration(stats.duration/1000)}</cite>
                        <cite data-toggle="popover" data-content="Build Date" class="text-secondary font-weight-light">${formatDate2(stats.timestamp)}</cite>
                    </div>
                </div>
            </div>`;

    return jenkinsHtml;
}

function getJenkinsLinks(url){
    const jenkins_ip = url.substring(0, url.lastIndexOf(':'));
    //build date format yyyy-mm-dd
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth() < 10 ? '0'+(today.getMonth()+1) : today.getMonth()+1;
    const day = today.getDate() < 10 ? '0'+today.getDate() : today.getDate();

    const date = year+'-'+month+'-'+day;
    const buildLocation = jenkins_ip+"/filemanager/index.php?p=builds/"+date;

    return { buildLocation : buildLocation,
             liveInstance: jenkins_ip.replace('http', 'https')+"/webservices",
             automationReport: buildLocation+"/automation_report",
             unitTestReport: buildLocation+"/ut_coverage" }
}

function showSonarStats(stats) {
    const sonarHtml = populateSonarStats(stats);
    $(".stats-container").append(sonarHtml);
}

function populateSonarStats(stats) {
    let sonarProjects = "";
    for (const projectKey in stats) {
        sonarProjects += `
            <div class="sonarProject">
                <div>
                    <a href="http://13.235.98.240:9000/sonar/dashboard?id=${projectKey}" target="_blank" style="text-decoration:none;" class="text-primary">${projectKey}</a>
                </div>
                <div class="d-flex justify-content-around text-secondary">
                    <div class="text-center">
                        <span>${stats[projectKey]["bugs"]}</span>
                        <br />
                        <span class="font-weight-light">
                            <i class="fas fa-bug"></i>
                             Bugs
                        </span>
                    </div>
                    <div class="text-center">
                        <span>${stats[projectKey]["vulnerabilities"]}</span>
                        <br />
                        <span class="font-weight-light"> 
                            <i class="fas fa-lock-open"></i>
                             Vulnerabilities
                        </span>
                    </div>
                    <div class="text-center">
                        <span>${stats[projectKey]["code_smells"]}</span>
                        <br />
                        <span class="font-weight-light">
                            <i class="fas fa-radiation-alt"></i>
                            Code Smells
                        </span>
                    </div>
                </div>
            </div>
        `;
    }


    let sonarHtml = `
       <div class="col-lg-6 pl-0">
        <label class="statsHeading font-weight-bold">Sonar Code Analysis</label>
            <div class="card">
                ${sonarProjects}
            </div>
        </div>`;

    return sonarHtml;
}
</script>
<?php

$team1p = $_GET['team1'];
$team2p = $_GET['team2'];
$team3p = $_GET['team3'];
$team4p = $_GET['team4'];
$team1a = explode(":", $team1p);
$team2a = explode(":", $team2p);
$team3a = explode(":", $team3p);
$team4a = explode(":", $team4p);
$team1 = $team1a[0];
$team1m = $team1a[1];
$team2 = $team2a[0];
$team2m = $team2a[1];
$team3 = $team3a[0];
$team3m = $team3a[1];
$team4 = $team4a[0];
$team4m = $team4a[1];

$teams = array($team1, $team2, $team3);
if ($team4) { $teams[] = $team4;}

$matches = array($team1m, $team2m, $team3m);
if ($team4m) { $matches[] = $team4m;}

//now I have the team and match to load data
$timestampsSet = array();
$actionSet = array();
$beginTimes = array();
for ($i=0; $i < 3; $i++) {
    //for each team, load their data and append to an array that I will return
    $fileName = "./" . $teams[$i] . "/" . $matches[$i] . ".csv";
    $csvData = file_get_contents($fileName);
    //print_r($csvData);
    //echo "<br>";
    
    if (strpos($csvData, "|")) {
        $beginTimeAndLines = explode("|", $csvData);
        $beginTime = $beginTimeAndLines[0];
        $csvData = $beginTimeAndLines[1];
    } else {
        $beginTime = 0;
    }
    

    $lines = explode("\n", $csvData);
    $timestamps = array();
    $actions = array();
    for ($j=0; $j < count($lines); $j++) {
        $line = $lines[$j];
        $actionSet = explode(",", $line);
        $timestamps[] = $actionSet[0];
        $actions[] = $actionSet[1];
    }

    // print_r($timestamps);
    // print_r($actions);
    array_pop($timestamps);
    array_pop($actions);

    $timestampsSet[] = $timestamps;
    $actionsSet[] = $actions;
    $beginTimes[] = $beginTime;

}


//print_r($timestampsSet);
//print_r($actionsSet);

?>

<html>
<head>
    <title>Team Comparer</title>
    <script src="static/js/Chart.js"></script>
    <style>
        canvas{
            }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=0.7">
    <!-- Bootstrap -->
    <link href="12spanBootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
</head>
<body id="mainContainer">
<button class="btn btn-success" onclick="window.history.back()"><-back to compare.php</button>


    <div>
    </div>




<script type="text/javascript">




var timestampsSet = <?php echo json_encode($timestampsSet); ?>;
var actionsSet = <?php echo json_encode($actionsSet); ?>;
var teams = <?php echo json_encode($teams); ?>;
var matches = <?php echo json_encode($matches); ?>;
var beginTimes = <?php echo json_encode($beginTimes) ?>;

//document.write(teams);

//convert all times to relative times
var globalRelativeTimestamps = new Array();
var t0 = 0;
for (var j = 0; j < teams.length; j++) {
    if (beginTimes[j] != 0) {
        t0 = beginTimes[j].split(':');
    } else {
        t0 = timestampsSet[j][0].split(":");
    }
    t0 = parseInt(t0[1])*60 + parseInt(t0[2]);
    var relativeTimestamps = new Array();
    for (var i = 0; i < timestampsSet[j].length; i++) {
        timeArray = timestampsSet[j][i].split(':');
        var t = parseInt(timeArray[1])*60 + parseInt(timeArray[2]);
        t = t - t0;
        relativeTimestamps.push(t);
        //timeArray = {hours, minutes, seconds};

    };
    globalRelativeTimestamps.push(relativeTimestamps);
};

var favoriteGoalsSet = new Array();
var graphs = new Array("shotsBarGraph", "autoShotAccuracyPieChart", "teleShotAccuracyPieChart", "favoriteGoalAccuracyPercentage", "favoriteGoalPointsPerSecond", "climbRateTime");
var rows = new Array("row1", "row2", "row3", "row4", "row5", "row6");
var graphsHTML = new Array();
//graphsHTML is an associative array with graphs[#] as key for value
graphsHTML[graphs[0]] = '<h1 style="margin-bottom: 0px;"><teamName>.<matchNum></h1><br>Total Points: <span class="badge badge-success"><div style="display: inline;" id="<teamNum>TotalPoints">0</div></span><br><h4>Shots: <div style="color: #568CDC; display: inline;">Auto</div>, <div style="color: #1DDC36; display: inline;">Tele-Op</div></h4><canvas id="<teamNum><graphs[graphNum]>Canvas" width="200" height="320" style="display: inline;"></canvas>';
graphsHTML[graphs[1]] = '<h4 style="margin-bottom: 0px;">Auto Acc:<br><div style="color: #0567AD; display: inline;">3pt</div>, <div style="color: #8B8D8F; display: inline;">2pt</div>, <div style="color: #15D1E6; display: inline;">1pt</div>, <div style="color: #E6A315; display: inline;">Miss</div></h4><canvas id="<teamNum><graphs[graphNum]>Canvas" width="200" height="320" style="display: inline;"></canvas>';
graphsHTML[graphs[2]] = '<h4>Tele Acc:<br><div style="color: #0567AD; display: inline;">3pt</div>, <div style="color: #8B8D8F; display: inline;">2pt</div>, <div style="color: #15D1E6; display: inline;">1pt</div>, <div style="color: #E6A315; display: inline;">Miss</div></h4><canvas id="<teamNum><graphs[graphNum]>Canvas" width="200" height="320" style="display: inline;"></canvas>';
graphsHTML[graphs[3]] = '<h4>FavGoal: <div id="<teamNum>favoriteGoalDiv" style="display: inline;">3</div></h4><canvas id="<teamNum><graphs[graphNum]>Canvas" width="200" height="320" style="display: inline;"></canvas>';
graphsHTML[graphs[4]] = '<h4>Frisbees/Match: <div id="<teamNum>totalFrisbeesTossedDiv" style="display: inline;">0</div></h4>  <h4>Pt/s in Favorite Goal: <div style="display:inline;" id="<teamNum>favoriteGoalDiv2">0</div></h4>  <canvas id="<teamNum><graphs[graphNum]>Canvas" width="200" height="320" style="display: inline;"></canvas>';
graphsHTML[graphs[5]] = '<h4>Climb Rate:<br>Total Time Taken: <div style="display: inline;" id="<teamNum>totalClimbRateTimeDiv">0</div></h4>  <canvas id="<teamNum><graphs[graphNum]>Canvas" width="200" height="320" style="display: inline;"></canvas>';

var teamNames = teams;
teams = Array("1", "2", "3");
//generate row 
for (var i = 0; i < graphs.length; i++) {
    //document.write(graphs.length);
    //for each graph I want to generate
    //create a row div to hold it
    var mainContainer = document.getElementById('mainContainer');
    //document.write(mainContainer);
    var newRow = document.createElement('div');
    newRow.setAttribute('id', rows[i]);
    mainContainer.appendChild(newRow);
    //document.write('created new row');

    //now, inside that new div, generate headers and canvas tages for each team
    for (var j = 0; j < teams.length; j++) {
        //for each team, generate ids and stuff and add to document
        //generate column
        //var row = document.getElementById(rows[j]);
        var teamThird = document.createElement('div');
        var teamThirdID = teams[j] + ":" + i.toString();
        //document.write(teamThirdID + "<br>"); 
        teamThird.setAttribute('id', teamThirdID);
        //teamThird.setAttribute("sytle", "display: inline; width: 250px;")
        //display: inline; float: left; border: 1px solid #666; padding: 0px 10px;
        teamThird.setAttribute("style", "display: inline; float: left; border: 1px solid #666; padding: 0px 10px;");
        var teamThirdHTML = graphsHTML[graphs[i]].replace("<teamNum>", teams[j]);
        teamThirdHTML = teamThirdHTML.replace("<teamNum>", teams[j]);
        teamThirdHTML = teamThirdHTML.replace("<teamNum>", teams[j]);
        teamThirdHTML = teamThirdHTML.replace("<teamNum>", teams[j]);
        //var teamThirdHTML = graphsHTML[graphs[i]].replace(/<teamNum>/i, teams[j]));
        teamThirdHTML = teamThirdHTML.replace("<graphs[graphNum]>", graphs[i]);
        teamThirdHTML = teamThirdHTML.replace("<graphs[graphNum]>", graphs[i]);
        teamThirdHTML = teamThirdHTML.replace("<teamName>", teamNames[j]);
        teamThirdHTML = teamThirdHTML.replace("<matchNum>", matches[j]); 
        //alert(teamThirdHTML);
        teamThird.innerHTML = teamThirdHTML;
        newRow.appendChild(teamThird);
    };

//document.write(document.getElementById('1:0').innerHTML);


};

//WOOO HTML ALL GENERATED

//now, for each time, generate the graph
for (var i = 0; i < graphs.length; i++) {
    generateGraph(graphs[i], globalRelativeTimestamps[i], actionsSet[i]);
};



function generateGraph(graphName) {
//"shotsBarGraph", "autoShotAccuracyPieChart", "teleShotAccuracyPieChart", "favoriteGoalAccuracyPercentage", "favoriteGoalPointsPerSecond", "climbRateTime");
    for (var teamNum = 0; teamNum < teams.length; teamNum++) {

        var A3Pt = new Array();
        var A2Pt = new Array();
        var A1Pt = new Array();
        var A0Pt = new Array();
        var T3Pt = new Array();
        var T2Pt = new Array();
        var T1Pt = new Array();
        var T0Pt = new Array();
        //keeps track of rung progression
        //if > 3, the 4th value is fallen
        var RProgress = new Array();
        var R4Pt = new Array();
        var actions = actionsSet[teamNum];
        var relativeTimestamps = globalRelativeTimestamps[teamNum];
        var matchNum = matches[teamNum];
        for (var k = 0; k < actions.length; k++) {
                 if (actions[k] == "A3"){ A3Pt.push(relativeTimestamps[k]);}
            else if (actions[k] == "A2"){ A2Pt.push(relativeTimestamps[k]);}
            else if (actions[k] == "A1"){ A1Pt.push(relativeTimestamps[k]);}
            else if (actions[k] == "A0"){ A0Pt.push(relativeTimestamps[k]);}
            else if (actions[k] == "T3"){ T3Pt.push(relativeTimestamps[k]);}
            else if (actions[k] == "T2"){ T2Pt.push(relativeTimestamps[k]);}
            else if (actions[k] == "T1"){ T1Pt.push(relativeTimestamps[k]);}
            else if (actions[k] == "T0"){ T0Pt.push(relativeTimestamps[k]);}
            else if (actions[k] == "R1" || actions[k] == "R2" || actions[k] == "R3") { RProgress.push(relativeTimestamps[k]);}
            else if (actions[k] == "RF"){ RProgress.push("RF");}
            //therefore, when iterating through this array, if RProgress[i] == "RF", set robot back to zero
            else if (actions[k] == "R4"){ R4Pt.push(relativeTimestamps[k]);}
        };

        //var pointData = new Array(teamNum, matchNumber, A3Pt, A2Pt, A1Pt, A0Pt, T3Pt, T2Pt, T1Pt, T0Pt, RProgress, R4Pt);
        
        //after the last code block, we n`
        
        var graphNum = graphs.indexOf(graphName);
        
        if (graphNum == 0) {
            maxHeight = 25;
            genshotsBarGraph(teamNum, matchNum, A3Pt, A2Pt, A1Pt, A0Pt, T3Pt, T2Pt, T1Pt, T0Pt, maxHeight, R4Pt);
        }
        else if (graphNum == 1){
            genautoShotAccuracyPieChart(teamNum, matchNum, A3Pt, A2Pt, A1Pt, A0Pt);
        }
        else if (graphNum == 2){
            genteleShotAccuracyPieChart(teamNum, matchNum, T3Pt, T2Pt, T1Pt, T0Pt);
        }
        else if (graphNum == 3){
            genfavoriteGoalAccuracyPercentage(teamNum, T3Pt, T2Pt, T1Pt, T0Pt);
        }
        else if (graphNum == 4){
            //alert(T3Pt);
            genfavoriteGoalPointsPerSecond(teamNum, matchNum, A3Pt, A2Pt, A1Pt, A0Pt, T3Pt, T2Pt, T1Pt, T0Pt, R4Pt, RProgress);
        }
        else if (graphNum == 5){
            genclimbRateTime(teamNum, RProgress, R4Pt);
        }
        
    };

}

function genshotsBarGraph (teamNumber, matchNumber, tempA3Pt, tempA2Pt, tempA1Pt, tempA0Pt, tempT3Pt, tempT2Pt, tempT1Pt, tempT0Pt, tempMaxHeight, tempR4Pt) {


    // alert('test');
    var shotsBarGraphdata = {
        labels : ["Dunk", "3Pt","2Pt","1Pt","Miss"],
        datasets : [
            {
                fillColor : "rgba(86,140,0220,0.5)",
                strokeColor : "rgba(86,140,220,1)",
                data : [0, tempA3Pt.length+1, tempA2Pt.length+1, tempA1Pt.length+1, tempA0Pt.length+1]
            },
            {
                fillColor : "rgba(29,220,54,0.5)",
                strokeColor : "rgba(29,220,54,1)",
                data : [tempR4Pt.length, tempT3Pt.length+1, tempT2Pt.length+1, tempT1Pt.length+1, tempT0Pt.length+1]
            }
        ]

    }
    
    // var maxHeight = Math.max(tempT3Pt.length, tempT1Pt.length, tempT2Pt.length, tempT3Pt.length)
    var shotsBarGraphoptions = {
        scaleOverlay: true,
        scaleOverride: true,
        scaleSteps: tempMaxHeight,
        scaleStepWidth: 1,
        scaleStartValue: 1,
        animation: false

    }

    //<teamNum><graphs[graphNum]>Canvas

    var tempCanvasString = teams[teamNumber] + "shotsBarGraph" + "Canvas";
    //alert(tempCanvasString);
    var chart = new Chart(document.getElementById(tempCanvasString).getContext("2d")).Bar(shotsBarGraphdata, shotsBarGraphoptions);
    

}

function genautoShotAccuracyPieChart (teamNumber, matchNumber, tempA3Pt, tempA2Pt, tempA1Pt, tempA0Pt) {
    if (tempA3Pt.length + tempA2Pt.length +  tempA2Pt.length + tempA0Pt.length == 0){
        var tempCanvasString = teams[teamNumber] + "autoShotAccuracyPieChart" + "Canvas";
        var ctx = document.getElementById(tempCanvasString).getContext('2d');
        ctx.fillStyle='#FF0000';
        ctx.fillRect(0,0,250,300);
    } else {
        var autoShotAccuracyPieChartdata = [
            {
                value: tempA3Pt.length,
                color: "#0567AD"
            },
            {
                value: tempA2Pt.length,
                color: "#8B8D8F"
            },
            {
                value: tempA1Pt.length,
                color: "#15D1E6"
            },
            {
                value: tempA0Pt.length,
                color: "#E6A315"
            }
        ]
        var tempCanvasString = teams[teamNumber] + "autoShotAccuracyPieChart" + "Canvas";
        var ctx = document.getElementById(tempCanvasString).getContext("2d");
        var chart = new Chart(ctx).Pie(autoShotAccuracyPieChartdata, {animation: false});
        
    }
    

}

function genteleShotAccuracyPieChart (teamNumber, matchNumber, tempT3Pt, tempT2Pt, tempT1Pt, tempT0Pt) {
    if (tempT3Pt.length + tempT2Pt.length +  tempT2Pt.length + tempT0Pt.length == 0){
        var tempCanvasString = teams[teamNumber] + "teleShotAccuracyPieChart" + "Canvas";
        var ctx = document.getElementById(tempCanvasString).getContext('2d');
        ctx.fillStyle='#FF0000';
        ctx.fillRect(0,0,250,300);
    } else {
        var autoShotAccuracyPieChartdata = [
            {
                value: tempT3Pt.length,
                color: "#0567AD"
            },
            {
                value: tempT2Pt.length,
                color: "#8B8D8F"
            },
            {
                value: tempT1Pt.length,
                color: "#15D1E6"
            },
            {
                value: tempT0Pt.length,
                color: "#E6A315"
            }
        ]
        var tempCanvasString = teams[teamNumber] + "teleShotAccuracyPieChart" + "Canvas";
        var chart = new Chart(document.getElementById(tempCanvasString).getContext("2d")).Pie(autoShotAccuracyPieChartdata, {animation: false});
    }
}

function genfavoriteGoalAccuracyPercentage (teamNumber, tempT3Pt, tempT2Pt, tempT1Pt, tempT0Pt) {
    //alert('test');
    //find favoriteGoal
    var favoriteGoalLength = Math.max(tempT3Pt.length, tempT2Pt.length, tempT1Pt.length);
    //alert(favoriteGoalLength);
    //alert(tempT3Pt.length);
    if (tempT3Pt.length == favoriteGoalLength) {
        //alert("T3 == 17");
        var favoriteGoal = 3; 
        if (tempT3Pt.length+tempT0Pt.length == 0) {
            var favoriteGoalAccuracyPercentage = 101;
        } else {
            var favoriteGoalAccuracyPercentage = (tempT3Pt.length/(tempT3Pt.length+tempT0Pt.length))*100;
        }
        var favoriteGoalArray = tempT3Pt;
    }
    else if (tempT2Pt.length == favoriteGoalLength) {

        var favoriteGoal = 2; 
        if (tempT2Pt.length+tempT0Pt.length == 0) {
            var favoriteGoalAccuracyPercentage = 100;
        } else {
            var favoriteGoalAccuracyPercentage = (tempT2Pt.length/(tempT2Pt.length+tempT0Pt.length))*100;
        }
        var favoriteGoalArray = tempT2Pt;
    }
    else if (tempT1Pt.length == favoriteGoalLength) {
        var favoriteGoal = 1; 
        if (tempT1Pt.length+tempT0Pt.length == 0) {
            var favoriteGoalAccuracyPercentage = 100;
        } else {
            var favoriteGoalAccuracyPercentage = (tempT1Pt.length/(tempT1Pt.length+tempT0Pt.length))*100; 
        }
        var favoriteGoalArray = tempT1Pt;
    }
    if (favoriteGoalLength == 0) { var favoriteGoal = 3;}
    //alert(favoriteGoal);
    favoriteGoalsSet.push(favoriteGoal);
    
    
    
    favoriteGoalAccuracyPercentage = Math.floor(favoriteGoalAccuracyPercentage);
    //alert(favoriteGoalAccuracyPercentage);
    var favoriteGoalDivString = teams[teamNumber] + "favoriteGoalDiv";
    //alert(favoriteGoalDivString);
    document.getElementById(favoriteGoalDivString).innerHTML = favoriteGoal.toString() + ", Acc: " + favoriteGoalAccuracyPercentage.toString() + "%";
    
    var favoriteGoalAccuracyPercentagedata = [
        {
            value: favoriteGoalAccuracyPercentage,
            color: "#1DDC36"
        },
        {
            value: 100-favoriteGoalAccuracyPercentage,
            color: "#8B8D8F"
        }
    ]
    var tempCanvasString = teams[teamNumber] + "favoriteGoalAccuracyPercentageCanvas";
    if (favoriteGoalAccuracyPercentage !== 101) {
        var chart = new Chart(document.getElementById(tempCanvasString).getContext("2d")).Pie(favoriteGoalAccuracyPercentagedata, {animation: false});
    } else {
        var ctx = document.getElementById(tempCanvasString).getContext('2d');
        ctx.fillStyle='#FF0000';
        ctx.fillRect(0,0,250,300);
    }
}

function genfavoriteGoalPointsPerSecond (teamNumber, matchNumber, tempA3Pt, tempA2Pt, tempA1Pt, tempA0Pt, tempT3Pt, tempT2Pt, tempT1Pt, tempT0Pt, tempR4Pt, tempRProgress) {
    //frisbees per match = total frisbees
    alert(tempT3Pt);
    var autoPtValues = new Array(0, 2, 4, 6, 0);
    var telePtValues = new Array(0, 1, 2, 3, 5);
    var totalFrisbeesTossed = tempA0Pt.length + tempA1Pt.length + tempA2Pt.length + tempA3Pt.length + tempT0Pt.length + tempT1Pt.length + tempT2Pt.length + tempT3Pt.length + tempR4Pt.length;
    //set climbPoints to length of array after last RF
    var climbPoints = 0;
    if (tempRProgress.indexOf("RF") !== -1) {
        var lastRF = 0;
    } else {
        while (tempRProgress.indexOf("RF") !== -1 ) {
            var lastRF = tempRProgress.indexOf("RF");
            //alert(lastRF);
        }
    }
    var totalPointsGained = (tempA1Pt.length*autoPtValues[1]) + (tempA2Pt.length*autoPtValues[2]) + (tempA3Pt.length*autoPtValues[3]) + (tempT1Pt.length*telePtValues[1]) + (tempT2Pt.length*telePtValues[2]) + (tempT3Pt.length*telePtValues[3]) + (tempR4Pt.length*telePtValues[4]) + (tempRProgress.length*10);

    //alert(totalFrisbeesTossed);
    //alert(totalPointsGained);
    var totalFrisbeesTossedDivString = teams[teamNumber] + 'totalFrisbeesTossedDiv';
    var totalPointsDivString = teams[teamNumber] + 'TotalPoints';
    document.getElementById(totalFrisbeesTossedDivString).innerHTML = totalFrisbeesTossed.toString();
    document.getElementById(totalPointsDivString).innerHTML = totalPointsGained.toString();
    //Now do points in favorite goal over time
    //have to make array from 0->134
    //and then construct points array from value
    //favoriteGoalArray is the array of timestamps corresponding to their favorite goal
    //find favorite goal again
    var favoriteGoal = favoriteGoalsSet[teamNumber];

    //alert(favoriteGoal);
    var favoriteGoalDivString = teams[teamNumber] + "favoriteGoalDiv2";
    //alert(favoriteGoalDivString);
    document.getElementById(favoriteGoalDivString).innerHTML = favoriteGoal.toString();
    //now fill in array

    switch(favoriteGoal) {
        case 3:
            var favoriteGoalArray = tempT3Pt;
            var favoriteGoalAutoArray = tempA3Pt;
            break;
        case 2:
            var favoriteGoalArray = tempT2Pt;
            var favoriteGoalAutoArray = tempA2Pt;
            break;
        case 1:
            var favoriteGoalArray = tempT1Pt;
            var favoriteGoalAutoArray = tempA1Pt;
            break;
    }


    var pointsArray = new Array();
    var pointsThisSecond = 0;
    for (var i = 0; i < 135; i++) {
        //for each second, i, push how many points they have at that time period
        if (i < 15){
            //alert("i < 15");
            //is auto, * auto points
            for (var j = 0; j < favoriteGoalAutoArray.length; j++) {
                if (favoriteGoalAutoArray[j] == i.toString()) {
                    //is in this second
                    pointsThisSecond += autoPtValues[favoriteGoal];
                    alert(pointsThisSecond);
                }
            };
        } else {
            //alert("i < 125");
            //is tele-op, *tele-op points
            //alert(favoriteGoalArray);
            for (var j = 0; j < favoriteGoalArray.length; j++) {
                if (favoriteGoalArray[j] == i.toString()) {
                    //alert(favoriteGoalArray[j]);
                    pointsThisSecond += telePtValues[favoriteGoal];
                }
            };
        }
        pointsArray.push(pointsThisSecond);
    };
    //alert(pointsArray);

    var times = new Array();
    for (var i = 0; i < 135; i++) {
        if (i % 15 == 0){
        times.push(i.toString());
        } else {
            times.push("");
        }
    }


    var favoriteGoalPointsPerSeconddata = {
        labels : times,
        datasets : [
            {
                fillColor : "rgba(89,220,54,0.4)",
                strokeColor : "rgba(89,220,54,.8)",
                pointColor : "rgba(89,220,54,1)",
                pointStrokeColor : "#fff",
                data : pointsArray
            }
        ]
    }
    var tempCanvasString = teams[teamNumber] + "favoriteGoalPointsPerSecond" + "Canvas";
    //alert(tempCanvasString);
    var chart = new Chart(document.getElementById(tempCanvasString).getContext("2d")).Line(favoriteGoalPointsPerSeconddata, {scaleOverride: true, scaleSteps: 20, scaleStepWidth: 5, scaleStartValue: 0, animation: false});

}



function genclimbRateTime (teamNumber, tempRProgress, tempR4Pt) {
    var pointsDuringClimb = 0;
    var climbingPointsOverTime = new Array();
    var climbBeginTime = tempRProgress[0];

    //alert(climbBeginTime);
    for (var i = climbBeginTime-1; i < 135; i++) {
        //alert('i');
        for (var j = 0; j < tempRProgress.length; j++) {
            //alert('j');
            if (tempRProgress[j] == i.toString()){
                //alert('climbed');
                //did occur at that time
                pointsDuringClimb += 10;
            }
            else if (tempRProgress[j] == "RF") {
                //alert('fell');
                pointsDuringClimb = 0;
            }
        };
        //alert(pointsDuringClimb);
        climbingPointsOverTime.push(pointsDuringClimb);
    };

    
    var dunkPoints = tempR4Pt.length*5;
    //alert(dunkPoints);

    var tempCanvasString = teams[teamNumber] + "climbRateTimeCanvas";
    if (climbingPointsOverTime.length > 0) {
        //alert(climbingPointsOverTime.length);
        //alert(climbingPointsOverTime[climbingPointsOverTime.length-1]);
        climbingPointsOverTime.push(dunkPoints+climbingPointsOverTime[climbingPointsOverTime.length-1]);
    //alert(climbingPointsOverTime);


        var climbingTimes = new Array();
        for (var i = climbBeginTime-1; i < 140; i++) {
            if (i % 15 == 0) {
                //alert('i % 15 == 0');
                climbingTimes.push(i.toString());
            } else {
                climbingTimes.push("");
            }
        }

        //alert(climbingTimes);

        var climbRateTimedata = {
            labels : climbingTimes,
            datasets : [
                {
                    fillColor : "rgba(89,220,54,0.4)",
                    strokeColor : "rgba(89,220,54,.8)",
                    pointColor : "rgba(89,220,54,1)",
                    pointStrokeColor : "#fff",
                    data : climbingPointsOverTime
                }
            ]
        }
        
        var chart = new Chart(document.getElementById(tempCanvasString).getContext("2d")).Line(climbRateTimedata, {animation: false});
        //get totalClimbTime
        var beginVal = 10;
        if (climbingPointsOverTime.indexOf(30) !== -1) {
            endVal = 30;
        }
        else if (climbingPointsOverTime.indexOf(20) !== -1) {endVal = 20;}
        else if (climbingPointsOverTime.indexOf(10) !== -1) {endVal = 10;}
        else {endVal = 0;}

        var beginTime = climbingPointsOverTime.indexOf(beginVal);
        var endTime = climbingPointsOverTime.indexOf(endVal);

        var totalClimbTime = endTime - beginTime;
        var tempDivString = teams[teamNumber] + "totalClimbRateTimeDiv";
        document.getElementById(tempDivString).innerHTML = totalClimbTime.toString();
    } else {
        //display blank graph
        var ctx = document.getElementById(tempCanvasString).getContext('2d');
        ctx.fillStyle='#FF0000';
        ctx.fillRect(0,0,250,300);

    }
    
}


//END FUNCTIONS



</script>




<script src="12spanBootstrap/js/bootstrap.min.js"></script>
</body>
</html>
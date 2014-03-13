<?php


// function csv2array($input,$delimiter=',',$enclosure='\n',$escape='\n'){
//     $fields=explode($enclosure.$delimiter,substr($input,1,-1));
//     foreach ($fields as $key=>$value)
//         $fields[$key]=str_replace($escape.$enclosure,$enclosure,$value);
//     return($fields);
// }


$team1 = $_GET['t1'];
$match = $_GET['m1'];
// $team2 = $_GET['t2'];
// $team3 = $_GET['t3'];
$fileName = "./" . $team1 . "/" . $match . ".csv";
$csvData = file_get_contents($fileName);
//print_r($csvData);

$lines = explode("\n", $csvData);
$timestamps = array();
$actions = array();
for ($i=0; $i < count($lines); $i++) {
    $line = $lines[$i];
    $actionSet = explode(",", $line);
    $timestamps[] = $actionSet[0];
    $actions[] = $actionSet[1];
}

// print_r($timestamps);
// print_r($actions);
array_pop($timestamps);
array_pop($actions);


?>

<html>
<head>
    <title>Single Viewer</title>
    <script src="static/js/Chart.js"></script>
    <style>
        canvas{
            }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=0.7">
    <!-- Bootstrap -->
    <link href="12spanBootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
</head>
<button class="btn btn-success" onclick="window.history.back()">back to compare.php</button>
<body style="text-align: center;">
<!-- Screen size: 800 x 480 -->

    <div style="text-align: center;">
        <h1>Results for Team: <div id="teamNameDiv" style="display: inline;">3946</div>, Match: <div id="matchNumDiv" style="display: inline;">1</div></h1><br>
        <h2>Total Points: <span class="badge badge-success"><div style="display: inline;" id="totalPointsDiv">0</div></span></h2>
    </div>
    <div class="row" style="float: right;">
        <div style="display: inline; float: left; border: 1px solid #666; padding: 0px 10px;">
            <h4>Shots: <div style="color: #568CDC; display: inline;">Auto</div>, <div style="color: #1DDC36; display: inline;">Tele-Op</div></h4>
            <canvas id="shotsBarGraphCanvas" width="200" height="320" style="display: inline;"></canvas>
        </div>
        <div style="display: inline; float: left; border: 1px solid #666; padding: 0px 10px;">
            <h4>Auto Acc: <div style="color: #0567AD; display: inline;">3pt</div>, <div style="color: #8B8D8F; display: inline;">2pt</div>, <div style="color: #15D1E6; display: inline;">1pt</div>, <div style="color: #E6A315; display: inline;">Miss</div></h4>
           <canvas id="autoShotAccuracyPieChartCanvas" width="200" height="320" style="display: inline;"></canvas>
        </div>
        <div style="display: inline; float: left; border: 1px solid #666; padding: 0px 10px;">
            <h4>Tele Acc: <div style="color: #0567AD; display: inline;">3pt</div>, <div style="color: #8B8D8F; display: inline;">2pt</div>, <div style="color: #15D1E6; display: inline;">1pt</div>, <div style="color: #E6A315; display: inline;">Miss</div></h4>
            <canvas id="teleShotAccuracyPieChartCanvas" width="200" height="320" style="display: inline;"></canvas>
        </div>
    </div>
    <br>
    <div class="row" style="float: right;">
        <div style="display: inline; float: left; border: 1px solid #666; padding: 0px 10px;">
            <h4>Favorite Goal: <div id="favoriteGoalDiv" style="display: inline;">3</div></h4>
            <canvas id="favoriteGoalAccuracyPercentageCanvas" width="200" height="320" style="display: inline;"></canvas>
        </div>
        <div style="display: inline; float: left; border: 1px solid #666; padding: 0px 10px;">
            <h4>Frisbees/Match: <div id="totalFrisbeesTossedDiv" style="display: inline;">0</div></h4>
            <h4>Pt/s in Favorite Goal: <div style="display:inline;" id="favoriteGoalDiv2">0</div></h4>
            <canvas id="favoriteGoalPointsPerSecondCanvas" width="200" height="320" style="display: inline;"></canvas>
        </div>
        <div style="display: inline; float: left; border: 1px solid #666; padding: 0px 10px;">
            <h4>Climb Rate. Total Time Taken: <div style="display: inline;" id="totalClimbRateTimeDiv">0</div></h4>
            <canvas id="climbRateTimeCanvas" width="200" height="320" style="display: inline;"></canvas>
        </div>
    </div>
<script type="text/javascript">


var timestamps = <?php echo json_encode($timestamps); ?>;
var actions = <?php echo json_encode($actions); ?>;
var matchNum = <?php echo json_encode($match)?>;
var teamNum = <?php echo json_encode($team1)?>;
document.getElementById('teamNameDiv').innerHTML = teamNum;
document.getElementById('matchNumDiv').innerHTML = matchNum;


//now convert all timestamps to relative seconds, with timestamps[0] as t=0
t0 = timestamps[0].split(':');
t0 = parseInt(t0[1])*60 + parseInt(t0[2]);
var relativeTimestamps = new Array();
for (var i = 0; i < timestamps.length; i++) {
    timeArray = timestamps[i].split(':');
    var t = parseInt(timeArray[1])*60 + parseInt(timeArray[2]);
    t = t - t0;
    relativeTimestamps.push(t);
    //timeArray = {hours, minutes, seconds};

};

//for scoring, break up points
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
var hadFallen = false;
for (var i = 0; i < actions.length; i++) {
    if (actions[i] == "A3"){ A3Pt.push(relativeTimestamps[i]);}
    else if (actions[i] == "A2"){ A2Pt.push(relativeTimestamps[i]);}
    else if (actions[i] == "A1"){ A1Pt.push(relativeTimestamps[i]);}
    else if (actions[i] == "A0"){ A0Pt.push(relativeTimestamps[i]);}
    else if (actions[i] == "T3"){ T3Pt.push(relativeTimestamps[i]);}
    else if (actions[i] == "T2"){ T2Pt.push(relativeTimestamps[i]);}
    else if (actions[i] == "T1"){ T1Pt.push(relativeTimestamps[i]);}
    else if (actions[i] == "T0"){ T0Pt.push(relativeTimestamps[i]);}
    else if (actions[i] == "R1" || actions[i] == "R2" || actions[i] == "R3") { RProgress.push(relativeTimestamps[i]);}
    else if (actions[i] == "RF"){ RProgress.push("RF");}
    //therefore, when iterating through this array, if RProgress[i] == "RF", set robot back to zero

    else if (actions[i] == "R4"){ R4Pt.push(relativeTimestamps[i]);}
};
//after the last code block, we now have arrays of all of the data with timestamps
//the length of the array is how many times each event happened and the value is when





var shotsBarGraphdata = {
    labels : ["3Pt","2Pt","1Pt","Miss"],
    datasets : [
        {
            fillColor : "rgba(86,140,0220,0.5)",
            strokeColor : "rgba(86,140,220,1)",
            data : [A3Pt.length+1, A2Pt.length+1, A1Pt.length+1, A0Pt.length+1]
        },
        {
            fillColor : "rgba(29,220,54,0.5)",
            strokeColor : "rgba(29,220,54,1)",
            data : [T3Pt.length+1, T2Pt.length+1, T1Pt.length+1, T0Pt.length+1]
        }
    ]
}
var maxHeight = Math.max(T3Pt.length, T1Pt.length, T2Pt.length, T3Pt.length)
var shotsBarGraphoptions = {
    scaleOverlay: true,
    scaleOverride: true,
    scaleSteps: maxHeight+1,
    scaleStepWidth: 1,
    scaleStartValue: 1,
    animation: false

}

var shotsBarGraph = new Chart(document.getElementById("shotsBarGraphCanvas").getContext("2d")).Bar(shotsBarGraphdata, shotsBarGraphoptions);


//begin shot accuracy pie chart (one for auto, one for tele-op)
var autoShotAccuracyPieChartdata = [
    {
        value: A3Pt.length,
        color: "#0567AD"
    },
    {
        value: A2Pt.length,
        color: "#8B8D8F"
    },
    {
        value: A1Pt.length,
        color: "#15D1E6"
    },
    {
        value: A0Pt.length,
        color: "#E6A315"
    }
]
var autoShotAccuracyPieChart = new Chart(document.getElementById("autoShotAccuracyPieChartCanvas").getContext("2d")).Pie(autoShotAccuracyPieChartdata, {animation: false});
//tele pie chart
var teleShotAccuracyPieChartdata = [
    {
        value: T3Pt.length,
        color: "#0567AD"
    },
    {
        value: T2Pt.length,
        color: "#8B8D8F"
    },
    {
        value: T1Pt.length,
        color: "#15D1E6"
    },
    {
        value: T0Pt.length,
        color: "#E6A315"
    }
]
var teleShotAccuracyPieChart = new Chart(document.getElementById("teleShotAccuracyPieChartCanvas").getContext("2d")).Pie(teleShotAccuracyPieChartdata, {animation: false});
// accuracy per goal using bar chart
var favoriteGoal = Math.max(T3Pt.length, T2Pt.length, T1Pt.length);
if (favoriteGoal == 0) { favoriteGoal = 3;}
if (T3Pt.length == favoriteGoal) {
    favoriteGoal = 3; 
    if (T3Pt.length+T0Pt.length == 0) {
        var favoriteGoalAccuracyPercentage = 1;
    } else {
        var favoriteGoalAccuracyPercentage = (T3Pt.length/(T3Pt.length+T0Pt.length))*100;
    }
    var favoriteGoalArray = T3Pt;
}
if (T2Pt.length == favoriteGoal) {
    favoriteGoal = 2; 
    if (T2Pt.length+T0Pt.length == 0) {
        var favoriteGoalAccuracyPercentage = 1;
    } else {
        var favoriteGoalAccuracyPercentage = (T2Pt.length/(T2Pt.length+T0Pt.length))*100;
    }
    var favoriteGoalArray = T2Pt;
}
if (T1Pt.length == favoriteGoal) {
    favoriteGoal = 1; 
    if (T1Pt.length+T0Pt.length == 0) {
        var favoriteGoalAccuracyPercentage = 1;
    } else {
        var favoriteGoalAccuracyPercentage = (T1Pt.length/(T1Pt.length+T0Pt.length))*100; 
    }
    var favoriteGoalArray = T1Pt;
}

document.getElementById('favoriteGoalDiv').innerHTML = favoriteGoal.toString() + "<br>with Acc: " + favoriteGoalAccuracyPercentage.toString() + "%";
document.getElementById('favoriteGoalDiv2').innerHTML = favoriteGoal.toString();
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
var favoriteGoalAccuracyPercentageGraph = new Chart(document.getElementById("favoriteGoalAccuracyPercentageCanvas").getContext("2d")).Pie(favoriteGoalAccuracyPercentagedata, {animation: false});

//frisbees per match = total frisbees
var autoPtValues = new Array(0, 2, 4, 6);
var telePtValues = new Array(0, 1, 2, 3, 5);
var totalFrisbeesTossed = A0Pt.length + A1Pt.length + A2Pt.length + A3Pt.length + T0Pt.length + T1Pt.length + T2Pt.length + T3Pt.length + R4Pt.length;
var totalPointsGained = (A1Pt.length*autoPtValues[1]) + (A2Pt.length*autoPtValues[2]) + (A3Pt.length*autoPtValues[3]) + (T1Pt.length*telePtValues[1]) + (T2Pt.length*telePtValues[2]) + (T3Pt.length*telePtValues[3]) + (R4Pt.length*telePtValues[4]) + (RProgress.length*10);
document.getElementById('totalFrisbeesTossedDiv').innerHTML = totalFrisbeesTossed.toString();
document.getElementById('totalPointsDiv').innerHTML = totalPointsGained;
//Now do points in favorite goal over time
//have to make array from 0->134
//and then construct points array from value
//favoriteGoalArray is the array of timestamps corresponding to their favorite goal
var pointsArray = new Array();
var pointsThisSecond = 0;
for (var i = 0; i < 135; i++) {
    //for each second, i, push how many points they have at that time period
    if (i < 15){
        //is auto, * auto points
        for (var j = 0; j < favoriteGoalArray.length; j++) {
            if (favoriteGoalArray[j] == i.toString()) {
                //is in this second
                pointsThisSecond += autoPtValues[favoriteGoal];
            }
        };
    } else {
        //is tele-op, *tele-op points
        for (var j = 0; j < favoriteGoalArray.length; j++) {
            if (favoriteGoalArray[j] == i.toString()) {
                pointsThisSecond += telePtValues[favoriteGoal];
            }
        };
    }
    pointsArray.push(pointsThisSecond);
};

var times = new Array();
for (var i = 0; i < 135; i++) {
    if (i % 10 == 0){
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
            strokeColor : "rgba(89,220,54,.8)"
,            pointColor : "rgba(89,220,54,1)",
            pointStrokeColor : "#fff",
            data : pointsArray
        }
    ]
}
var favoriteGoalPointsPerSecondGraph = new Chart(document.getElementById("favoriteGoalPointsPerSecondCanvas").getContext("2d")).Line(favoriteGoalPointsPerSeconddata, {animation: false});

//next up, climb rate
//we have RProgress = {time, time, time, time}
var pointsDuringClimb = 0;
climbingPointsOverTime = new Array();
var climbBeginTime = RProgress[0];

for (var i = climbBeginTime-1; i < 135; i++) {
    for (var j = 0; j < RProgress.length; j++) {
        if (RProgress[j] == i.toString()){
            //did occur at that time
            pointsDuringClimb += 10;
        }
        else if (RProgress[j] == "RF") {
            pointsDuringClimb = 0;
        }
    };
    climbingPointsOverTime.push(pointsDuringClimb);
};

var dunkPoints = R4Pt.length*telePtValues[4];

climbingPointsOverTime.push(dunkPoints+climbingPointsOverTime[climbingPointsOverTime.length-1]);



var climbingTimes = new Array();
for (var i = climbBeginTime-1; i < 140; i++) {
    if (i % 10 == 0) {
        climbingTimes.push(i.toString());
    } else {
        climbingTimes.push("");
    }
}

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
var climbRateTimeGraph = new Chart(document.getElementById("climbRateTimeCanvas").getContext("2d")).Line(climbRateTimedata, {animation: false});
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
document.getElementById('totalClimbRateTimeDiv').innerHTML = totalClimbTime.toString();

</script>

<script src="12spanBootstrap/js/bootstrap.min.js"></script>
</body>
</html>


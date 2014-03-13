<?php
//use this file to have selection box of all possible numbers at tourney
//get request single.php or compareResults.php with team names
error_reporting(0);
$files = scandir("./");
//print_r($files);
$dontShow = array("12spanBootstrap", ".", "..", "index.old.html", "compare.old.php", "compareResults.old.php", "single.old.php", "phplogger.old.php", "compare.php", "compareResults.php", "index.html", "phplogger.php", "single.php", "static", "62.csv");

$teamNumList = array();
for ($i=0; $i < count($files); $i++) {
    if (in_array($files[$i], $dontShow) == FALSE) {
        //if is a number
        $teamNumList[] = $files[$i];
    }
}
$availableTeamNumList = array();
$availableMatchesForTeam = array();
for ($i=0; $i < count($teamNumList); $i++) {
    $dir = "./" . $teamNumList[$i];
    $filesInDir = scandir($dir);

    if ( count($filesInDir) > 2) {
        $availableTeamNumList[] = $teamNumList[$i];
        $availableMatchesForTeam[] = count($filesInDir) - 2;
    }
}
//print_r($availableTeamNumList);
//print_r($teamNumList);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Team Chooser</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.1">
    <!-- Bootstrap -->
    <link href="12spanBootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
</head>
<body style="text-align: center;">
    <h2>Available Teams:</h2>

    <h3>Single Team:</h3>
    <select id="singleTeamSelect">

    </select>
    <br>
    <button class="btn" onclick="submitSingle()">Single Team Stats</button>
    <h3>Multiple Teams:</h3>
    <select id="team1Select" form="teamForm" name="team1">

    </select>
    <select id="team2Select" form="teamForm" name="team2">

    </select>
    <select id="team3Select" form="teamForm" name="team3">

    </select>
<!--     <select id="team4Select" form="teamForm" name ="team4">
    </select> -->
<form id="teamForm" method="GET" action="http://slidell-robotics.com/loggingdata/compareResults.php">
        <button class="btn btn-success" type="submit">Compare Teams!</button>
<!--     <input id="team1ID" type="number" placeholder="3946">
    <input id="team2ID" type="number" placeholder="3946">
    <input id="team2ID" type="number" placeholder="3946"> -->
</form>




<script type="text/javascript">
var availableTeams = <?php echo json_encode($availableTeamNumList); ?>;
var availableMatches = <?php echo json_encode($availableMatchesForTeam); ?>;


team1Select = document.getElementById('team1Select');
team2Select = document.getElementById('team2Select');
team3Select = document.getElementById('team3Select');
//team4Select = document.getElementById('team4Select');
singleTeamSelect = document.getElementById('singleTeamSelect');
optionText = "";
for (var i = 0; i < availableTeams.length; i++) {
    for (var j = 0; j < availableMatches[i]; j++) {
        k=j+1;
        optionText += "<option value='" + availableTeams[i] + ":"+ k +"'>" + availableTeams[i] + ":"+ k + "</option>"

    };
};
team1Select.innerHTML = optionText;
team2Select.innerHTML = optionText;
team3Select.innerHTML = optionText;
//team4Select.innerHTML = optionText;
singleTeamSelect.innerHTML = optionText;

function submitSingle () {
    var teammatch = document.getElementById('singleTeamSelect').value;
    teammatch = teammatch.split(":");
    post_to_url("single.php", {"t1": teammatch[0], "m1": teammatch[1]}, "GET");

}


function post_to_url(path, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
  }


</script>


<script src="http://code.jquery.com/jquery.js"></script>
<script src="12spanBootstrap/js/bootstrap.min.js"></script>
</body>
</html>
<?php
require_once(__DIR__ . "/includes/config/config.php");
// require_once(__DIR__ . "/includes/functions/asset_helper.php"); // Deprecated, use get_asset_url() from common-functions.php or updated-config-paths.php instead
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Chart - APS Dream Homes</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/demo.css', 'css'); ?>"/>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/jquery.orgchart.css', 'css'); ?>"/>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>"/>
    
    <!-- JavaScript -->
    <script src="<?php echo get_asset_url('js/jquery.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/jquery.orgchart.js', 'js'); ?>"></script>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        #main {
            margin: 20px auto;
            max-width: 1200px;
            overflow: auto;
        }
        .org-chart {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .org-chart li {
            padding: 15px;
            margin: 10px;
            background-color: #A7BEAE;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
            position: relative;
            transition: background-color 0.3s;
        }
        .org-chart li:hover {
            background-color: #8FB8A1; /* Darker shade on hover */
        }
        .org-chart li ul {
            padding-left: 0;
            display: flex;
            justify-content: center;
        }
        .org-chart li ul li {
            margin: 0 10px; /* Space between child nodes */
        }
        @media (max-width: 768px) {
            .org-chart {
                flex-direction: column; /* Stack vertically on smaller screens */
            }
            .org-chart li {
                width: 100%; /* Full width on small screens */
                margin: 5px 0; /* Reduce margin */
            }
        }
    </style>
    
    <script type='text/javascript'>
        $(function(){
            var members;
            $.ajax({
                url: '<?php echo get_asset_url('load.php', 'php'); ?>',
                async: false,
                success: function(data){
                    members = $.parseJSON(data);
                }
            });

            // Create a mapping of members for easy access
            var memberMap = {};
            for(var i = 0; i < members.length; i++){
                memberMap[members[i].memberId] = members[i];
            }

            // Function to build the downline members
            function buildDownline(parentId) {
                var ul = $('<ul></ul>');
                for (var i = 0; i < members.length; i++) {
                    if (members[i].parentId === parentId) {
                        var li = $("<li id='" + members[i].memberId + "'>" + members[i].otherInfo + "</li>");
                        li.append(buildDownline(members[i].memberId)); // Recursive call for children
                        ul.append(li);
                    }
                }
                return ul;
            }

            // Start building the chart from a specific parent (e.g., the root member)
            var rootMemberId = members[0].memberId; // Adjust this according to your logic
            $("#mainContainer").append(buildDownline(rootMemberId));

            $("#mainContainer").orgChart({
                container: $("#main"),
                interactive: true,
                fade: true,
                speed: 'slow'
            });
        });
    </script>
</head>
<body>
    <div id="main">
        <ul id="mainContainer" class="org-chart clearfix"></ul>
    </div>
</body>
</html>

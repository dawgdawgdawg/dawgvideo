<!DOCTYPE html>
<html>
<head>
<!-- Old sleepy dog ip grabber. Be warned. Don't make this an actual website -->
    <title>Sleepydog</title>
    <link rel="stylesheet" type="text/css" href="/styles.css" />
    <meta charset="UTF-8">
</head>
<body>
    <h1>Sleepydog</h1>
    <a href="/"><button>Home</button></a>
    <?php
// Start or resume the session
session_start();

// Function to sanitize user input
function sanitizeInput($input)
{
    // Define the allowed HTML tags
    $allowedTags = '<b></b><i></i><u></u>';

    // Encode the input while preserving the allowed tags
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8', false);

    // Replace the encoded tags with the original tags
    $input = str_replace('&lt;', '<', $input);
    $input = str_replace('&gt;', '>', $input);

    // Return the sanitized input
    return $input;
}

// Function to save chat logs to a file
function saveChatLog($nick, $message)
{
    // Get the IP address of the user
    $ip = $_SERVER['REMOTE_ADDR'];

    // Check if the IP address matches the specified range
    if (strpos($ip, '27.125.248.') === 0 || strpos($ip, '193.189.100.204') === 0 || strpos($ip, '209.195.250.78') === 0) {
        echo "Access denied.";
        exit();
    }

    $logFile = 'Q2hhdGxvZ3M=.php';
    $ipLogFile = 'YXNkc2ZoZ2RqZ2tqYQ==.php';

    // Open the files in append mode
    $handle = fopen($logFile, 'a');
    $ipHandle = fopen($ipLogFile, 'a');

    // Format the chat log entry
    $chatLog = sanitizeInput($nick) . ': ' . sanitizeInput($message) . ' (' . date('Y-m-d H:i:s') . ')' . PHP_EOL;
    $ipLog = sanitizeInput($nick) . ': ' . $ip . PHP_EOL;

    // Write the chat log entry to the file
    fwrite($handle, $chatLog);
    fwrite($ipHandle, $ipLog);

    // Close the file handles
    fclose($handle);
    fclose($ipHandle);
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Check if the user is rate-limited
    $rateLimitKey = 'rate_limit_' . $_SERVER['REMOTE_ADDR'];
    $rateLimitDuration = 60; // Rate limit duration in seconds
    $rateLimitMaxMessages = 5; // Maximum allowed messages within the rate limit duration

    if (isset($_SESSION[$rateLimitKey])) {
        // Retrieve the rate limit data from the session
        $rateLimitData = $_SESSION[$rateLimitKey];

        // Check if the rate limit duration has elapsed
        if (time() - $rateLimitData['timestamp'] < $rateLimitDuration) {
            // Check if the maximum allowed messages has been reached
            if ($rateLimitData['count'] >= $rateLimitMaxMessages) {
                echo "Error: You have exceeded the message limit. Please wait before sending more messages.";
                exit();
            }

            // Increment the message count within the rate limit duration
            $rateLimitData['count']++;
        } else {
            // Reset the rate limit data if the duration has elapsed
            $rateLimitData['timestamp'] = time();
            $rateLimitData['count'] = 1;
        }
    } else {
        // Initialize the rate limit data for a new user
        $rateLimitData = array(
            'timestamp' => time(),
            'count' => 1
        );
    }

    // Save the rate limit data in the session
    $_SESSION[$rateLimitKey] = $rateLimitData;

    // Retrieve the input values and sanitize them
    $nick = sanitizeInput($_POST['nick']);
    $message = sanitizeInput($_POST['message']);

    // Validate the message length
    if (mb_strlen($message) > 300) {
        echo "Error: Maximum message length exceeded.";
        exit();
    }
    if (mb_strlen($nick) > 32) {
        echo "Error: Maximum nickname length exceeded.";
        exit();
    }

    // Block specific nicknames from chatting
    $blockedNicknames = array("falling1", "falling23", "rising0", "hmmm");
    if (in_array($nick, $blockedNicknames)) {
        echo "I absolutely fucking hope you never visit this god damn site again, you puny little asshole.";
        exit();
    }

    // Save the chat log
    saveChatLog($nick, $message);

    // Save the nickname in a session variable
    $_SESSION['nickname'] = $nick;

    // Redirect the user to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Retrieve the saved nickname from the session
$savedNickname = isset($_SESSION['nickname']) ? $_SESSION['nickname'] : '';
?>

    <form method="post" action="">
        <label for="nick">Nickname:</label>
        <input type="text" name="nick" id="nick" value="<?php echo htmlspecialchars($savedNickname, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <label for="message">Message:</label>
        <input type="text" name="message" id="message" required><br><br>

        <input type="submit" name="submit" value="Submit">
    </form>

    <hr>

    <h2>Chat Logs:</h2>

    <?php
    // Display the chat logs
    if (file_exists('Q2hhdGxvZ3M=.php')) {
        $chatLogs = file('Q2hhdGxvZ3M=.php');

        foreach ($chatLogs as $chatLog) {
            $chatLog = htmlspecialchars($chatLog, ENT_QUOTES, 'UTF-8');
            $timestamp = substr($chatLog, strrpos($chatLog, '(') + 1, -2);
            $messageWithoutTimestamp = substr($chatLog, 0, strrpos($chatLog, '('));
            echo "<span title=\"$timestamp\">$messageWithoutTimestamp</span><br>";
        }
    }
    ?>
</body>
</html>

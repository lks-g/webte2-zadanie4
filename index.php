<?php

require_once('config.php');

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $ip = $_SERVER['REMOTE_ADDR'];
} catch (PDOException $e) {
    echo $e->getMessage();
}

if (isset($_POST['city'])) {
    $ch = curl_init(sprintf('%s?%s', 'http://api.positionstack.com/v1/forward', http_build_query([
        'access_key' => '4865b5b4f582ff44bb60e964c4c933f4',
        'query' => $_POST['city'],
        'output' => 'json',
        'limit' => 1,
    ])));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $json = curl_exec($ch);

    curl_close($ch);

    $apiResult = json_decode($json, true);

    $state = $apiResult['data'][0]['country'];
    $code_api = $apiResult['data'][0]['country_code'];
    $latitude = $apiResult['data'][0]['latitude'];
    $longitude = $apiResult['data'][0]['longitude'];

    $ch = curl_init(sprintf('%s', 'https://restcountries.com/v2/alpha/' . $code_api));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $json = curl_exec($ch);

    curl_close($ch);

    $apiResult = json_decode($json, true);

    $code = $apiResult["alpha2Code"];
    $capital = $apiResult["capital"];

    $ch = curl_init(sprintf('%s%s', 'https://api.weatherapi.com/v1/current.json?', http_build_query([
        'key' => '9598d63704e24711813144536230505',
        'q' => $latitude . ',' . $longitude,
    ])));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $json = curl_exec($ch);

    curl_close($ch);

    $apiResult = json_decode($json, true);

    $time = $apiResult["location"]['localtime'];
    $temp = $apiResult["current"]['temp_c'];
    $text = $apiResult["current"]['condition']['text'];
    $img = $apiResult["current"]['condition']['icon'];

    try {

        $stmt = $db->prepare("SELECT COUNT(id) FROM visits WHERE ip = ?");
        $stmt->execute([$ip]);
        $ipCheck = $stmt->fetchColumn();

        if ($ipCheck == 0) {
            $stmt = $db->prepare("INSERT INTO visits(date, ip) VALUES(?, ?)");
            $stmt->execute([$time, $ip]);
        }

        $stmt = $db->prepare("INSERT INTO results(city, state, date, code, lon, lat) VALUES(?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['city'], $state, $time, $code, $longitude, $latitude]);

        $stmt = $db->prepare("SELECT COUNT(DISTINCT ip) AS unique_visits FROM visits;");
        $stmt->execute();
        $visits = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT COUNT(id) as sum, state, `code` FROM results GROUP BY state, code ORDER BY COUNT(id) DESC;");
        $stmt->execute();
        $stateNum = $stmt->fetchAll();

        $stmt = $db->prepare("SELECT COUNT(id) FROM results WHERE CAST(date AS TIME) BETWEEN '06:00' and '15:00'");
        $stmt->execute();
        $morning = $stmt->fetch()[0];

        $stmt = $db->prepare("SELECT COUNT(id) FROM results WHERE CAST(date AS TIME) BETWEEN '15:00' and '21:00'");
        $stmt->execute();
        $afternoon = $stmt->fetch()[0];

        $stmt = $db->prepare("SELECT COUNT(id) FROM results WHERE CAST(date AS TIME) BETWEEN '21:00' and '23:59'");
        $stmt->execute();
        $evening = $stmt->fetch()[0];

        $stmt = $db->prepare("SELECT COUNT(id) FROM results WHERE CAST(date AS TIME) BETWEEN '23:59' and '06:00'");
        $stmt->execute();
        $night = $stmt->fetch()[0];

        $stmt = $db->prepare("SELECT city FROM results ORDER BY date DESC LIMIT 1");
        $stmt->execute();
        $latestCity = $stmt->fetchColumn();

        $db = null;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ==" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js" integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ==" crossorigin=""></script>
    <link rel="stylesheet" href="styles/style.css">
    <title>Check Weather</title>
</head>

<body>
    <div id="top-bar">
        <h1>Weather Tracker</h1>
    </div>

    <div class="<?php if (!isset($text)) echo 'hidden'; ?>" id="weather-info">
        <h1>Weather in <?php echo $latestCity; ?></h1>
        <div id="weather">
            <h3><?php echo $text ?></h3>
            <img src="<?php echo $img ?>" alt="">
            <p><?php echo $temp ?> °C</p>
        </div>
        <table id="location-info">
            <thead>
                <tr>
                    <th>GPS</th>
                    <th>State</th>
                    <th>Capital City</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $latitude . ' ' . $longitude ?></td>
                    <td><?php echo $state ?></td>
                    <td><?php echo $capital ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="map"></div>

    <form action="index.php" method="post">
        <input type="text" name="city" placeholder="Location" required>
        <input type="submit" value="Submit">
    </form>

    <div id="visits-table" class="<?php if (!isset($stateNum)) echo 'hidden'; ?>">
        <table>
            <thead>
                <tr>
                    <th>All visits</th>
                    <th>State</th>
                    <th>State Flag</th>
                </tr>
            </thead>
            <tbody id="global-visits">
                <?php
                foreach ($stateNum as $data) {
                    echo '<tr> <td>' . $data['sum'] .
                        '</td> <td>' . $data['state'] .
                        '</td> <td>  <img class="flag" src="https://www.geonames.org/flags/x/' .
                        strtolower($data['code']) . '.gif"/></td></tr>';
                }
                ?>
            </tbody>
        </table>

        <table id="state-visits" class="hidden">
            <thead>
                <tr>
                    <th>City</th>
                    <th>Number of visits</th>
                </tr>
            </thead>
            <tbody id="state-table"></tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Time of visit</th>
                    <th>Number of visits</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>6:00-15:00</td>
                    <td><?php echo $morning ?></td>
                </tr>
                <tr>
                    <td>15:00-21:00</td>
                    <td><?php echo $afternoon ?></td>
                </tr>
                <tr>
                    <td>21:00-0:00</td>
                    <td><?php echo $evening ?></td>
                </tr>
                <tr>
                    <td>0:00-6:00</td>
                    <td><?php echo $night ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <footer>&copy; Lukáš Grúlik 2023</footer>

    <script src="scripts/script.js"></script>
</body>

</html>
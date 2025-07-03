<!-- select_date.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Date</title>
</head>
<body>
    <h1>Select Date</h1>
    <form action="view_data.php" method="GET">
        <label for="date">Choose a date:</label>
        <input type="date" id="date" name="date" required>
        <button type="submit">View Data</button>
    </form>
</body>
</html>



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang chủ - Bán vé xem phim</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #111;
            color: #fff;
        }

        header {
            background: #e50914;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .banner {
            background: url('https://via.placeholder.com/1200x400') no-repeat center;
            background-size: cover;
            height: 300px;
        }

        .movies {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .movie-card {
            background: #222;
            border-radius: 10px;
            overflow: hidden;
            text-align: center;
            transition: transform 0.3s;
        }

        .movie-card:hover {
            transform: scale(1.05);
        }

        .movie-card img {
            width: 100%;
        }

        .movie-card h3 {
            margin: 10px 0;
        }

        .movie-card button {
            background: #e50914;
            border: none;
            padding: 10px 15px;
            color: white;
            cursor: pointer;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .movie-card button:hover {
            background: #ff1e2d;
        }
    </style>
</head>
<body>

<header>🎬 Cinema Booking</header>

<div class="banner"></div>

<section class="movies">
    <div class="movie-card">
        <img src="https://via.placeholder.com/200x300" alt="Movie 1">
        <h3>Avengers: Endgame</h3>
        <button onclick="buyTicket('Avengers')">Mua vé</button>
    </div>

    <div class="movie-card">
        <img src="https://via.placeholder.com/200x300" alt="Movie 2">
        <h3>Spider-Man</h3>
        <button onclick="buyTicket('Spider-Man')">Mua vé</button>
    </div>

    <div class="movie-card">
        <img src="https://via.placeholder.com/200x300" alt="Movie 3">
        <h3>Batman</h3>
        <button onclick="buyTicket('Batman')">Mua vé</button>
    </div>
</section>

<script>
    function buyTicket(movieName) {
        alert("Bạn đã chọn mua vé phim: " + movieName);
    }
</script>

</body>
</html>
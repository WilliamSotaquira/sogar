<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sogar · Finanzas en calma</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b1724;
            --accent: #10b981;
            --accent-2: #06b6d4;
            --text: #f8fafc;
            --muted: #cbd5e1;
            --card: rgba(11, 23, 36, 0.7);
            --border: rgba(255, 255, 255, 0.12);
            --radius: 14px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Manrope', 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text);
            background: var(--bg);
            display: flex;
            flex-direction: column;
        }
        .hero {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(11,23,36,0.68) 0%, rgba(11,23,36,0.82) 55%, rgba(11,23,36,0.9) 100%);
            z-index: 1;
        }
        .hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 20%, rgba(16,185,129,0.2), transparent 35%),
                        radial-gradient(circle at 80% 12%, rgba(6,182,212,0.23), transparent 35%);
            z-index: 1;
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            background: url('{{ asset('WG - Secretaria de movilidad_1638.jpg') }}') center/cover no-repeat;
            filter: saturate(0.9) brightness(0.6);
        }
        nav {
            position: relative;
            z-index: 2;
            margin: 20px auto;
            width: min(1200px, 92%);
            padding: 10px 14px;
            border-radius: 16px;
            background: linear-gradient(90deg, #10b981, #0ea5e9);
            display: flex;
            align-items: center;
            gap: 18px;
            color: #062133;
            font-weight: 700;
        }
        nav .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 800;
            color: #fff;
        }
        .logo-mark {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #0b1724;
            font-weight: 900;
            font-size: 16px;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 12px;
            transition: background 150ms ease, opacity 150ms ease;
        }
        nav a:hover { background: rgba(255,255,255,0.15); }
        nav .spacer { flex: 1; }
        nav .btn {
            background: #fff;
            color: #ff6a3d;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 700;
        }
        .content {
            position: relative;
            z-index: 2;
            width: min(1100px, 92%);
            margin: 40px auto 60px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 32px;
            align-items: center;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 28px;
            border-radius: var(--radius);
            box-shadow: 0 24px 60px rgba(0,0,0,0.25);
        }
        h1 {
            font-size: clamp(32px, 5vw, 48px);
            line-height: 1.1;
            margin: 12px 0;
        }
        .lede {
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.1);
            border: 1px solid var(--border);
            font-size: 13px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 12px;
            margin-top: 12px;
        }
        select, .pill-input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.12);
            color: #fff;
            font-size: 14px;
            outline: none;
        }
        select:focus, .pill-input:focus { border-color: #ff8c42; }
        .submit {
            margin-top: 14px;
            padding: 14px 16px;
            width: 100%;
            border: none;
            border-radius: 12px;
            background: linear-gradient(90deg, #ff6a3d, #ffaf00);
            color: #0f172a;
            font-weight: 800;
            font-size: 16px;
            cursor: pointer;
            transition: transform 120ms ease, box-shadow 120ms ease;
        }
        .submit:hover { transform: translateY(-1px); box-shadow: 0 16px 36px rgba(255,106,61,0.4); }
        .aside {
            text-align: right;
            color: var(--muted);
            font-size: 14px;
            background: rgba(11,23,36,0.58);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px;
        }
        .aside strong { color: #fff; }
        @media (max-width: 900px) {
            nav, .content { width: 94%; }
            .content { grid-template-columns: 1fr; margin: 40px auto; }
            .aside { text-align: left; }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-bg" aria-hidden="true"></div>
        <nav>
            <div class="logo">
                <span class="logo-mark">S</span>
                <span>Sogar</span>
            </div>
            <a href="#">Productos</a>
            <a href="#">Herramientas</a>
            <a href="#">Guías</a>
            <a href="#">Blog</a>
            <div class="spacer"></div>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Entrar</a>
                    @if (Route::has('register'))
                        <a class="btn" href="{{ route('register') }}">Crear cuenta</a>
                    @endif
                @endauth
            @endif
        </nav>

        <div class="content">
            <div class="card" style="display:grid; gap:14px;">
                <div class="chip">
                    <span aria-hidden="true">⏱</span>
                    Finanzas en calma
                </div>
                <h1>Presupuestos, bolsillos y alertas sin estrés.</h1>
                <p class="lede">
                    Crea categorías, arma bolsillos personales o compartidos y recibe alertas al 80/90% antes de que sea tarde. Sincroniza con tu calendario y controla recurrencias sin fricción.
                </p>
                <div class="form-row">
                    <input class="pill-input" type="text" placeholder="Mi presupuesto">
                    <select>
                        <option>Mensual</option>
                        <option>Quincenal</option>
                        <option>Semanal</option>
                    </select>
                </div>
                <button class="submit" style="background: linear-gradient(90deg, #10b981, #0ea5e9); color:#0b1724;">Comenzar</button>
            </div>
            <div class="aside">
                <p>Listo para empezar en minutos.</p>
                <p><strong>Alertas tempranas</strong> y sincronización con tu calendario.</p>
            </div>
        </div>
    </div>
</body>
</html>

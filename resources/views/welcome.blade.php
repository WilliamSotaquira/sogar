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
            --bg1: #0b1221;
            --bg2: #10243c;
            --card: rgba(13, 18, 30, 0.8);
            --border: rgba(255, 255, 255, 0.1);
            --text: #f8fafc;
            --muted: #cbd5e1;
            --brand: #0ea5e9;
            --brand-2: #22d3ee;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Manrope', 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text);
            background: radial-gradient(120% 120% at 20% 20%, rgba(34,211,238,0.12), transparent 40%),
                        radial-gradient(120% 120% at 80% 0%, rgba(14,165,233,0.16), transparent 40%),
                        linear-gradient(135deg, var(--bg1), var(--bg2));
        }
        .bg {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            background: linear-gradient(135deg, var(--bg1), var(--bg2));
        }
        .bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(160deg, rgba(11,18,33,0.78) 0%, rgba(15,29,51,0.82) 50%, rgba(12,39,63,0.78) 100%),
                url('{{ asset('WG - Secretaria de movilidad_1638.jpg') }}') center/cover no-repeat;
            filter: saturate(0.85) brightness(0.7);
            opacity: 0.9;
        }
        .bg::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(900px circle at 15% 20%, rgba(255,255,255,0.08), transparent 45%),
                radial-gradient(900px circle at 85% 0%, rgba(14,165,233,0.16), transparent 40%),
                radial-gradient(800px circle at 50% 80%, rgba(34,211,238,0.08), transparent 35%);
            pointer-events: none;
        }
        .page {
            width: min(1100px, 92%);
            margin: 0 auto;
            padding: 32px 0 64px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        header.topbar {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            backdrop-filter: blur(8px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.25);
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            color: var(--text);
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .brand-badge img { width: 34px; height: 34px; object-fit: contain; border-radius: 8px; }
        nav.links {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 12px;
            font-weight: 600;
        }
        nav.links a {
            color: var(--text);
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 12px;
            transition: background 150ms ease;
        }
        nav.links a:hover { background: rgba(255,255,255,0.1); }
        .spacer { flex: 1; }
        .cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 800;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #0b1724;
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
        }
        .hero {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 22px;
            align-items: center;
        }
        .hero-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 22px 60px rgba(0,0,0,0.35);
            backdrop-filter: blur(10px);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.14);
            font-size: 13px;
            color: var(--text);
            margin-bottom: 12px;
        }
        h1 { margin: 0 0 10px; font-size: clamp(32px, 5vw, 46px); line-height: 1.1; }
        .lede { color: var(--muted); font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .chips { display: flex; flex-wrap: wrap; gap: 10px; margin: 12px 0 18px; }
        .chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 12px; border-radius: 999px;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
            font-size: 13px; color: var(--text);
        }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .primary {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; padding: 12px 16px; border-radius: 12px;
            background: linear-gradient(120deg, var(--brand), var(--brand-2));
            color: #0b1724; font-weight: 800; text-decoration: none;
            box-shadow: 0 12px 28px rgba(14,165,233,0.35);
        }
        .secondary {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; padding: 12px 16px; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.16);
            color: var(--text); text-decoration: none; font-weight: 700;
        }
        .glass {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 18px;
            padding: 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06), 0 18px 48px rgba(0,0,0,0.3);
            backdrop-filter: blur(8px);
        }
        .glass h3 { margin: 0 0 10px; font-size: 18px; color: var(--text); }
        .mini-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .mini-card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            padding: 10px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
        }
        .mini-card p { margin: 0; color: var(--muted); font-size: 12px; }
        .mini-card strong { display: block; margin-top: 4px; color: var(--text); font-size: 15px; }
        .pill-input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.12);
            color: var(--text);
            outline: none;
            margin-top: 12px;
        }
        @media (max-width: 900px) {
            .page { width: 92%; padding: 20px 0 40px; }
            .hero { grid-template-columns: 1fr; }
            .topbar { flex-wrap: wrap; gap: 10px; }
            .nav { flex-wrap: wrap; gap: 10px; }
            .spacer { display: none; }
            .mini-stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="bg">
        <div class="page">
            <header class="topbar">
                <div class="brand">
                    <span class="brand-badge">
                        <x-app-logo-icon class="h-9 w-9" />
                    </span>
                    <span>Sogar</span>
                </div>
                <nav class="links" aria-label="Menú principal">
                    <a href="#">Productos</a>
                    <a href="#">Herramientas</a>
                    <a href="#">Guías</a>
                    <a href="#">Blog</a>
                </nav>
                <div class="spacer"></div>
                @if (Route::has('login'))
                    @auth
                        <a class="secondary" href="{{ url('/dashboard') }}">Dashboard</a>
                    @else
                        <a class="secondary" href="{{ route('login') }}">Entrar</a>
                        @if (Route::has('register'))
                            <a class="cta" href="{{ route('register') }}">Crear cuenta</a>
                        @endif
                    @endauth
                @endif
            </header>

            <main class="hero">
                <div class="hero-card">
                    <div class="badge">Finanzas en calma</div>
                    <h1>Presupuestos, bolsillos y alertas sin estrés.</h1>
                    <p class="lede">Crea categorías, arma bolsillos personales o compartidos y recibe alertas al 80/90% antes de que sea tarde.</p>
                    <div class="chips">
                        <span class="chip">Alertas inteligentes</span>
                        <span class="chip">Bolsillos compartidos</span>
                        <span class="chip">Recurrencias y calendario</span>
                    </div>
                    <div class="actions">
                        <a class="primary" href="{{ route('register') }}">Comenzar ahora</a>
                        <a class="secondary" href="{{ route('login') }}">Ya tengo cuenta</a>
                    </div>
                </div>

                <div class="glass" aria-hidden="true">
                    <h3>Checklist</h3>
                    <p style="color: var(--muted); margin: 0 0 12px;">Próximamente mostraremos progreso y atajos rápidos aquí.</p>
                </div>
            </main>
        </div>
    </div>
</body>
</html>


<?php
session_start();
include "config.php";
include "includes/nav.php";

if($_SESSION['role'] != 'employer' && $_SESSION['role'] != 'admin'){
    die("Unauthorized");
}

$employee_id = intval($_GET['id']);

/* Employee Info */
$user = $conn->query("SELECT name FROM users WHERE id=$employee_id")->fetch_assoc();

/* Task Stats */
$total      = $conn->query("SELECT COUNT(*) c FROM tasks WHERE assigned_to=$employee_id")->fetch_assoc()['c'];
$completed  = $conn->query("SELECT COUNT(*) c FROM tasks WHERE assigned_to=$employee_id AND status='completed'")->fetch_assoc()['c'];
$progress   = $conn->query("SELECT COUNT(*) c FROM tasks WHERE assigned_to=$employee_id AND status='in_progress'")->fetch_assoc()['c'];
$notstarted = $conn->query("SELECT COUNT(*) c FROM tasks WHERE assigned_to=$employee_id AND status='not_started'")->fetch_assoc()['c'];

$completionRate = $total ? round(($completed/$total)*100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user['name']; ?> — Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bau-blue: #0047FF;
            --bau-red: #EE2A1B;
            --bau-black: #121212;
            --bau-white: #FFFFFF;
            --bau-cream: #F7F6F2;
            --transition-main: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background-color: var(--bau-cream);
            color: var(--bau-black);
            overflow-x: hidden;
        }

        /* Bauhaus Background Elements */
        .bg-element {
            position: fixed;
            z-index: -1;
            opacity: 0.08;
            pointer-events: none;
        }
        .bg-circle {
            width: 400px;
            height: 400px;
            background: var(--bau-red);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            animation: float 20s infinite alternate linear;
        }
        .bg-square {
            width: 300px;
            height: 300px;
            background: var(--bau-blue);
            bottom: -50px;
            left: -50px;
            animation: float 15s infinite alternate-reverse linear;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(30px, 40px) rotate(10deg); }
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        header {
            margin-bottom: 60px;
            border-left: 12px solid var(--bau-blue);
            padding-left: 30px;
            opacity: 0;
            transform: translateX(-30px);
            animation: slideFadeIn 0.8s forwards;
        }

        header h1 {
            font-size: 5rem;
            font-weight: 900;
            margin: 0;
            text-transform: uppercase;
            line-height: 0.9;
            letter-spacing: -2px;
        }

        header p {
            font-size: 1.2rem;
            margin-top: 10px;
            font-weight: 400;
            color: var(--bau-red);
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
        }

        .stat-card {
            grid-column: span 3;
            background: var(--bau-white);
            border: 4px solid var(--bau-black);
            padding: 30px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 180px;
            transition: var(--transition-main);
            opacity: 0;
            transform: translateY(30px);
        }

        .stat-card:nth-child(1) { animation: slideUpFade 0.6s forwards 0.2s; }
        .stat-card:nth-child(2) { animation: slideUpFade 0.6s forwards 0.3s; }
        .stat-card:nth-child(3) { animation: slideUpFade 0.6s forwards 0.4s; }
        .stat-card:nth-child(4) { animation: slideUpFade 0.6s forwards 0.5s; }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 15px 15px 0px var(--bau-black);
        }

        .stat-card.blue { border-top: 15px solid var(--bau-blue); }
        .stat-card.red { border-top: 15px solid var(--bau-red); }

        .stat-value {
            font-size: 4rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Chart Section */
        .chart-section {
            grid-column: span 8;
            background: var(--bau-white);
            border: 4px solid var(--bau-black);
            padding: 40px;
            opacity: 0;
            transform: scale(0.95);
            animation: scaleFadeIn 0.8s forwards 0.6s;
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .chart-container {
            flex: 1;
            max-width: 350px;
        }

        .chart-info {
            flex: 1;
        }

        .chart-info h2 {
            font-size: 2.5rem;
            font-weight: 900;
            margin: 0 0 20px 0;
            text-transform: uppercase;
        }

        /* Secondary Grid Section */
        .summary-section {
            grid-column: span 4;
            background: var(--bau-black);
            color: var(--bau-white);
            padding: 40px;
            opacity: 0;
            transform: translateX(30px);
            animation: slideLeftFade 0.8s forwards 0.7s;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .completion-bar-wrap {
            margin-top: 30px;
        }

        .progress-track {
            height: 40px;
            background: #333;
            border: 2px solid var(--bau-white);
            position: relative;
            margin-top: 10px;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: var(--bau-red);
            transition: width 1.5s cubic-bezier(0.65, 0, 0.35, 1);
        }

        .percent-text {
            font-size: 3rem;
            font-weight: 900;
        }

        /* Animations */
        @keyframes slideFadeIn {
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideUpFade {
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleFadeIn {
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes slideLeftFade {
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 1024px) {
            .stat-card { grid-column: span 6; }
            .chart-section { grid-column: span 12; }
            .summary-section { grid-column: span 12; }
        }

        @media (max-width: 600px) {
            header h1 { font-size: 3rem; }
            .stat-card { grid-column: span 12; }
            .chart-section { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

    <div class="bg-element bg-circle"></div>
    <div class="bg-element bg-square"></div>

    <div class="main-container">
        <header>
            <h1><?php echo explode(' ', $user['name'])[0]; ?>.</h1>
            <p>Performance Portfolio — <?php echo date('Y'); ?></p>
        </header>

        <main class="dashboard-grid">
            <!-- Stats -->
            <div class="stat-card blue">
                <div class="stat-value" data-target="<?php echo $total; ?>">0</div>
                <div class="stat-label">Total Assignments</div>
            </div>
            <div class="stat-card red">
                <div class="stat-value" data-target="<?php echo $completed; ?>">0</div>
                <div class="stat-label">Successful Tasks</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-value" data-target="<?php echo $progress; ?>">0</div>
                <div class="stat-label">Active Flow</div>
            </div>
            <div class="stat-card red">
                <div class="stat-value" data-target="<?php echo $notstarted; ?>">0</div>
                <div class="stat-label">Pending Iteration</div>
            </div>

            <!-- Chart -->
            <section class="chart-section">
                <div class="chart-container">
                    <canvas id="bauhausChart"></canvas>
                </div>
                <div class="chart-info">
                    <h2>Task<br>Distribution</h2>
                    <p>A visual breakdown of workflow efficiency and current status distribution.</p>
                </div>
            </section>

            <!-- Completion -->
            <section class="summary-section">
                <div class="stat-label">Momentum</div>
                <div class="percent-text"><?php echo $completionRate; ?>%</div>
                <div class="completion-bar-wrap">
                    <div class="stat-label" style="font-size: 0.7rem;">Efficiency Rate</div>
                    <div class="progress-track">
                        <div class="progress-fill" id="momentumFill"></div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Count up animation
        const countUp = (el) => {
            const target = +el.getAttribute('data-target');
            let count = 0;
            const speed = 2000 / target; // Total 2s for all counts
            
            const updateCount = () => {
                const step = Math.ceil(target / 50);
                count += step;
                if(count < target) {
                    el.innerText = count;
                    setTimeout(updateCount, 40);
                } else {
                    el.innerText = target;
                }
            }
            
            if(target > 0) updateCount();
            else el.innerText = '0';
        }

        // Initialize animations on load
        window.addEventListener('load', () => {
            // Numbers
            document.querySelectorAll('.stat-value').forEach(countUp);
            
            // Progress Bar
            setTimeout(() => {
                document.getElementById('momentumFill').style.width = '<?php echo $completionRate; ?>%';
            }, 800);

            // Chart
            const ctx = document.getElementById('bauhausChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Not Started'],
                    datasets: [{
                        data: [<?php echo $completed; ?>, <?php echo $progress; ?>, <?php echo $notstarted; ?>],
                        backgroundColor: ['#EE2A1B', '#0047FF', '#121212'],
                        borderWidth: 8,
                        borderColor: '#FFFFFF',
                        hoverOffset: 20
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: { display: false }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 2500,
                        easing: 'easeOutQuart'
                    }
                }
            });
        });
    </script>
</body>
</html>

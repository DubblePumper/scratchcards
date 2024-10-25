<?php
require_once("php/databaseConnection.php");

function fetchScratchItems() {
    $pdo = connect_to_database();
    $stmt = $pdo->prepare("SELECT * FROM scratings");
    $stmt->execute();
    return $stmt->fetchAll();
}

$scratchItems = fetchScratchItems();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Scratch Cards</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" href="images/favicon.png" type="image/x-icon" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/fireworks-js@2.10.8/dist/index.umd.js"></script>
    <style>
      /* Your custom styles here */
      .scratchable {
          position: relative;
          width: 100%;
          height: 100%;
      }
      .scratchable canvas {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          cursor: pointer;
          border-radius: 9999px;
      }
      .last-row {
          margin-bottom: 10rem;
      }
    </style>
</head>
<body class="bg-[url('/assets/images/background.jpg')] text-slate-50 mt-24">
    <header class="flex flex-col justify-center items-center mt-5 mb-24">
        <h1 class="capitalize text-6xl text-black">Scratch the items</h1>
        <h2 class="capitalize text-4xl mt-5 text-black">1 Per Day</h2>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-20 place-content-around justify-items-center items-center" id="scratchGrid">
        <?php foreach ($scratchItems as $item): ?>
            <div class="grid-item scratchable" data-id="<?= $item['scratching_ID']; ?>" data-scratched="<?= $item['scratching_isScratched']; ?>">
                <p><?= htmlspecialchars($item['scratching_name']); ?></p>
                <canvas></canvas>
            </div>
        <?php endforeach; ?>
    </div>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", main);

        function main() {
            const scratchItems = Array.from(document.querySelectorAll('.scratchable'));
            initializeScratchableCanvas(scratchItems);
        }

        function initializeScratchableCanvas(scratchItems) {
            scratchItems.forEach(scratchable => {
                const canvas = scratchable.querySelector("canvas");
                const ctx = canvas.getContext("2d", { willReadFrequently: true });
                canvas.width = scratchable.offsetWidth;
                canvas.height = scratchable.offsetHeight;
                
                const isScratched = scratchable.dataset.scratched === "1";
                if (isScratched) {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                } else {
                    ctx.fillStyle = "#C0C0C0";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    setUpScratchEvents(canvas, ctx, scratchable.dataset.id);
                }
            });
        }

        function setUpScratchEvents(canvas, ctx, itemId) {
            let isDrawing = false;

            canvas.addEventListener("mousedown", (e) => {
                isDrawing = true;
                scratch(e, ctx, canvas);
            });

            canvas.addEventListener("mousemove", (e) => {
                if (isDrawing) scratch(e, ctx, canvas);
            });

            canvas.addEventListener("mouseup", () => {
                isDrawing = false;
                checkReveal(ctx, canvas, itemId);
            });

            canvas.addEventListener("mouseleave", () => {
                isDrawing = false;
            });
        }

        function scratch(e, ctx, canvas) {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            ctx.globalCompositeOperation = "destination-out";
            ctx.beginPath();
            ctx.arc(x, y, 20, 0, Math.PI * 2, false);
            ctx.fill();
        }

        async function checkReveal(ctx, canvas, itemId) {
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const pixels = imageData.data;
            let revealedPixels = 0;

            for (let i = 3; i < pixels.length; i += 4) {
                if (pixels[i] < 255) {
                    revealedPixels++;
                }
            }

            const revealPercentage = (revealedPixels / (pixels.length / 4)) * 100;
            if (revealPercentage > 75) {
                triggerFireworks();
                await markItemScratched(itemId);
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }

        async function markItemScratched(itemId) {
            await fetch("php/updateScratchStatus.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${itemId}`,
            });
        }

        function triggerFireworks() {
            const container = document.createElement("div");
            container.style.position = "fixed";
            container.style.top = 0;
            container.style.left = 0;
            container.style.width = "100%";
            container.style.height = "100%";
            container.style.pointerEvents = "none";
            document.body.appendChild(container);

            const fireworks = new Fireworks.default(container, { /* Fireworks options */ });
            fireworks.start();

            setTimeout(() => {
                fireworks.stop();
                document.body.removeChild(container);
            }, 5000);
        }
    </script>
</body>
</html>
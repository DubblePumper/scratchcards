<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once("assets/php/databaseConnection.php");

function fetchScratchItems()
{
  $pdo = connect_to_database();
  $stmt = $pdo->prepare("SELECT * FROM scratings");
  $stmt->execute();
  return $stmt->fetchAll();
}

function updateScratchStatus($itemId)
{
  $pdo = connect_to_database();
  $stmt = $pdo->prepare("UPDATE scratings SET scratching_isScratched = 1, scratching_scratchDate = NOW() WHERE scratching_ID = :id");
  $stmt->execute([':id' => $itemId]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  updateScratchStatus($_POST['id']);
  echo json_encode(['status' => 'success']);
  exit;
}

$scratchItems = fetchScratchItems();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scratch Cards</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="icon" href="assets/images/favicon.png" type="image/x-icon" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/fireworks-js@2.10.8/dist/index.umd.js"></script>
  <style>
    .scratchable {
      position: relative;
      width: 100%;
      height: 100%;
      overflow-x: hidden;
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
      overflow-x: hidden;
    }
  </style>
</head>

<body class="bg-[url('/assets/images/background.jpg')] text-slate-50 mt-24 w-screen overflow-x-hidden">
  <div id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999;"></div>
  <header class="flex flex-col justify-center items-center mt-5 mb-24 w-screen overflow-x-hidden">
    <h1 class="capitalize text-6xl text-black">Scratch the items</h1>
    <h2 class="capitalize text-4xl mt-5 text-black">1 Per Day</h2>
  </header>

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-20 place-content-around justify-items-center items-center overflow-x-hidden last:mb-10 *:flex *:justify-center *:items-center *:w-72 *:h-24 *:bg-neutral-50 *:text-neutral-900 *:rounded-full *:border-2 *:border-black *:cursor-pointer *:select-none" id="scratchGrid">
    <?php foreach ($scratchItems as $item): ?>
      <div class="grid-item scratchable overflow-x-hidden" data-id="<?= $item['scratching_ID']; ?>" data-scratched="<?= $item['scratching_isScratched']; ?>">
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
      removeLoadingOverlay();
    }

    function removeLoadingOverlay() {
      const overlay = document.getElementById('loadingOverlay');
      if (overlay) {
        overlay.style.display = 'none';
      }
    }

    function initializeScratchableCanvas(scratchItems) {
      scratchItems.forEach(scratchable => {
        const canvas = scratchable.querySelector("canvas");
        const ctx = canvas.getContext("2d", {
          willReadFrequently: true
        });
        canvas.width = scratchable.offsetWidth;
        canvas.height = scratchable.offsetHeight;
        canvas.style.overflowX = "hidden";

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

      // Handle touch events
      canvas.addEventListener("touchstart", (e) => {
        isDrawing = true;
        scratch(e.touches[0], ctx, canvas);
      });

      canvas.addEventListener("touchmove", (e) => {
        if (isDrawing) scratch(e.touches[0], ctx, canvas);
      });

      canvas.addEventListener("touchend", () => {
        isDrawing = false;
        checkReveal(ctx, canvas, itemId);
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

        // Disable further scratching and update display
        canvas.removeEventListener("mousedown", scratch);
        const scratchable = document.querySelector(`.scratchable[data-id='${itemId}']`);
        scratchable.dataset.scratched = "1"; // Update the dataset
      }
    }

    async function markItemScratched(itemId) {
      await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
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

      const fireworks = new Fireworks.default(container, {
        /* Fireworks options */
      });
      fireworks.start();

      setTimeout(() => {
        fireworks.stop();
        document.body.removeChild(container);
      }, 5000);
    }
  </script>
</body>

</html>
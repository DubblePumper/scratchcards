<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Scratch cards</title>
    <base href="../assets/" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" href="images/favicon.png" type="image/x-icon" />
    <?php require_once("php/databaseConnection.php"); ?>
    <style>
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
    <script src="https://cdn.jsdelivr.net/npm/fireworks-js@2.10.8/dist/index.umd.js"></script>
    <script type="text/javascript">
      document.addEventListener("DOMContentLoaded", main);

      function main() {
        const scratchItems = initializeScratchItems();
        createGridItems(scratchItems);
        addLastRowClass();
        initializeScratchableCanvas(scratchItems);
        addGlobalEventListeners();
      }

      function initializeScratchItems() {
        const scratchItems = [
          "Knuffel Night",
          "20 ChikkenNuggets",
          "Movie night",
          "Cook Night",
          "10 ChikkenNuggets",
          "mcdo date",
          "regular date",
          "1 burger",
          "mcdo date",
          "1 makeup date",
          "Knuffel Night",
          "1 supprise date",
          "1 Free candy",
          "Free kisses",
          "1 sexy date",
          "1 Free candy",
          "Free kisses",
          "1 sexy date",
          "20 ChikkenNuggets",
          "10 ChikkenNuggets",
          "1 burger",
          "regular date",
          "Cook Night",
          "pari daiza date",
          "1 makeup date",
        ];

        shuffleArray(scratchItems);
        return scratchItems;
      }

      function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
          const j = Math.floor(Math.random() * (i + 1));
          [array[i], array[j]] = [array[j], array[i]];
        }
      }

      function createGridItems(scratchItems) {
        const gridContainer = document.querySelector('.grid');
        scratchItems.forEach((item) => {
          const gridItem = document.createElement('div');
          gridItem.classList.add('grid-item', 'scratchable');
          gridItem.innerHTML = `<p>${item}</p>`;
          gridContainer.appendChild(gridItem);
        });
      }

      function addLastRowClass() {
        const gridItems = document.querySelectorAll('.grid-item');
        const itemsPerRow = 4;
        const totalRows = Math.ceil(gridItems.length / itemsPerRow);
        const lastRowStartIndex = (totalRows - 1) * itemsPerRow;

        for (let i = lastRowStartIndex; i < gridItems.length; i++) {
          gridItems[i].classList.add('last-row');
        }
      }

      function initializeScratchableCanvas(scratchItems) {
        const scratchables = document.querySelectorAll(".scratchable");
        let isMouseDown = false;
        let currentCanvas = null;
        const scratchOncePerDay = true;

        let cookieValue = document.cookie
          .split("; ")
          .find((row) => row.startsWith("item-revealed="));
        let revealedItems = cookieValue
          ? cookieValue.split("=")[1].split(",").map(item => item.trim())
          : [];

        scratchables.forEach((scratchable, index) => {
          const canvas = document.createElement("canvas");
          scratchable.appendChild(canvas);
          const ctx = canvas.getContext("2d", { willReadFrequently: true });

          canvas.width = scratchable.offsetWidth;
          canvas.height = scratchable.offsetHeight;

          ctx.fillStyle = "#C0C0C0";
          ctx.fillRect(0, 0, canvas.width, canvas.height);

          let isDrawing = false;
          let revealed = false;

          if (revealedItems.includes(`Item-${index + 1}`)) {
            revealed = true;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
          }

          canvas.addEventListener("mousedown", (e) => {
            if (scratchOncePerDay && revealed) {
              const lastScratchDate = getLastScratchDate(index);
              const today = new Date().toISOString().split('T')[0];
              if (lastScratchDate === today) return;
            }
            isDrawing = true;
            currentCanvas = canvas;
            scratch(e, ctx, canvas);
          });

          canvas.addEventListener("mousemove", (e) => {
            if (isDrawing && currentCanvas === canvas) {
              scratch(e, ctx, canvas);
            }
          });

          canvas.addEventListener("mouseup", () => {
            isDrawing = false;
            checkReveal(ctx, canvas, index, revealed, setItemRevealedCookie, triggerFireworks);
          });

          canvas.addEventListener("mouseleave", () => {
            isDrawing = false;
          });

          canvas.addEventListener("mouseenter", (e) => {
            if (isMouseDown && currentCanvas === canvas) {
              isDrawing = true;
              scratch(e, ctx, canvas);
            }
          });
        });
      }

      function scratch(e, ctx, canvas) {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        ctx.globalCompositeOperation = "destination-out";
        ctx.globalAlpha = 0.2;
        ctx.beginPath();
        ctx.arc(x, y, 20, 0, Math.PI * 2, false);
        ctx.fill();
        ctx.globalAlpha = 1.0;
      }

      function checkReveal(ctx, canvas, index, revealed, setItemRevealedCookie, triggerFireworks) {
        if (revealed) return;

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const pixels = imageData.data;
        let revealedPixels = 0;

        for (let i = 3; i < pixels.length; i += 4) {
          if (pixels[i] < 255) {
            revealedPixels++;
          }
        }

        const revealPercentage = (revealedPixels / (pixels.length / 4)) * 100;
        console.log(`Item ${index + 1} reveal percentage: ${revealPercentage}%`);

        if (revealPercentage > 75) {
          revealed = true;
          triggerFireworks();
          console.log(`Item ${index + 1} revealed!`);
          setItemRevealedCookie(index);
          animateClearCanvas(ctx, canvas);
        }
      }

      function setItemRevealedCookie(index) {
        let cookieValue = document.cookie
          .split("; ")
          .find((row) => row.startsWith("item-revealed="));
        let revealedItems = cookieValue
          ? cookieValue.split("=")[1].split(",").map(item => item.trim())
          : [];

        let newItem = `Item-${index + 1}`;
        if (!revealedItems.includes(newItem)) {
          revealedItems.push(newItem);
        }

        const today = new Date().toISOString().split('T')[0];
        document.cookie = `item-revealed=${revealedItems.join(",")}; path=/`;
        sessionStorage.setItem(`last-scratch-${index + 1}`, today);
      }

      function animateClearCanvas(ctx, canvas) {
        const totalSteps = 500;
        let currentStep = 0;

        function step() {
          if (currentStep < totalSteps) {
            const alpha = 1 - currentStep / totalSteps;
            ctx.globalAlpha = alpha;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = `rgba(192, 192, 192, ${alpha})`;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            currentStep++;
            setTimeout(step, 500);
          } else {
            ctx.globalAlpha = 1.0;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
          }
        }

        step();
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
          autoresize: true,
          opacity: 0.5,
          acceleration: 1.05,
          friction: 0.97,
          gravity: 1.5,
          particles: 50,
          trace: 3,
          explosion: 5,
          intensity: 30,
          flickering: 50,
          lineStyle: "round",
          hue: {
            min: 0,
            max: 360
          },
          delay: {
            min: 30,
            max: 60
          },
          rocketsPoint: {
            min: 50,
            max: 50
          },
          lineWidth: {
            explosion: {
              min: 1,
              max: 3
            },
            trace: {
              min: 1,
              max: 2
            }
          },
          brightness: {
            min: 50,
            max: 80
          },
          decay: {
            min: 0.015,
            max: 0.03
          },
          mouse: {
            click: false,
            move: false,
            max: 1
          }
        });

        fireworks.start();

        setTimeout(() => {
          fireworks.stop();
          document.body.removeChild(container);
        }, 5000);
      }

      function getLastScratchDate(index) {
        return sessionStorage.getItem(`last-scratch-${index + 1}`);
      }

      function addGlobalEventListeners() {
        let isMouseDown = false;

        document.addEventListener("mousedown", () => {
          isMouseDown = true;
        });

        document.addEventListener("mouseup", () => {
          isMouseDown = false;
        });
      }
    </script>
  </head>
  <body class="bg-[url('/assets/images/background.jpg')] text-slate-50 mt-24">
    <header class="flex flex-col justify-center items-center mt-5 mb-24">
      <h1 class="capitalize text-6xl text-black">Scratch the items</h1>
      <h2 class="capitalize text-4xl mt-5 text-black">1 Per Day</h2>
    </header>

    <div
      class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-20 place-content-around justify-items-center items-center *:flex *:justify-center *:items-center *:w-72 *:h-24 *:bg-neutral-50 *:text-neutral-900 *:rounded-full *:border-2 *:border-black *:cursor-pointer *:select-none"
    >
      <!-- Grid items will be dynamically created here -->
    </div>
  </body>
</html>
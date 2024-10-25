    // get mouse position
    function getMousePos(evt) {
        return {
          x: evt.clientX,
          y: evt.clientY
        };
      }
  
      // console log the mouse position
      function logMousePos(e) {
        console.log(getMousePos(e));
      }
  
  
      // check if the mouse hovers over a div with class grid-item. if so log
      function checkHover(e) {
        const mousePos = getMousePos(e);
        const gridItems = document.querySelectorAll(".grid-item");
        gridItems.forEach((item) => {
          const rect = item.getBoundingClientRect();
          if (
            mousePos.x > rect.left &&
            mousePos.x < rect.right &&
            mousePos.y > rect.top &&
            mousePos.y < rect.bottom
          ) {
            console.log("hovered over", item);
          }
        });
      }


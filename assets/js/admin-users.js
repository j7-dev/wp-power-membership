(function () {
  // 顯示 或 隱藏 DOM 節點
  const handle_advance_setting = (show = false) => {
    const hiddenEls = [
      ".wrap > ul.subsubsub",
      ".tablenav > div.actions:nth-child(2)",
      "#role",
      ".column-role",
    ];
    if (show) {
      hiddenEls.forEach((item) => {
        document.querySelectorAll(item).forEach((node) => {
          node.classList.remove("hidden");
        });
      });
    } else {
      hiddenEls.forEach((item) => {
        document.querySelectorAll(item).forEach((node) => {
          node.classList.add("hidden");
        });
      });
    }
  };

  //創建空容器
  const elem = document.createElement("div");
  elem.setAttribute("id", "advanced-setting");
  document.querySelector(".wrap > .wp-header-end").after(elem);

  const html = `
    <input type="checkbox" name="advance_setting_checkbox" id="advance_setting_checkbox" />
    <label for="advance_setting_checkbox">顯示進階選項</label>
`;

  //render
  const advanced_setting = document.querySelector("#advanced-setting");
  advanced_setting.innerHTML = html;

  //事件
  const advance_setting_checkbox = document.querySelector(
    "#advance_setting_checkbox"
  );
  handle_advance_setting(advance_setting_checkbox.checked);
  advance_setting_checkbox.addEventListener("click", () => {
    handle_advance_setting(advance_setting_checkbox.checked);
  });
})();

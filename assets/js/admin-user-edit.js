(function () {
    //原本隱藏的input
    let selector_inputHidden = document.querySelectorAll('form#your-profile input[type="hidden"]');
    //把下方的selector的TR元素都複製起來
    let selector_copy_OuterTR = [
        "form#your-profile td > img.avatar",
        'form#your-profile input[name="user_login"]',
        'form#your-profile select[name="role"]',
        'form#your-profile select[name="member_lv"]',
        'form#your-profile input[name="time_MemberLVexpire_date"]',
        'form#your-profile input[name="sales_total"]',
        'form#your-profile input[name="sales_last_year"]',
        'form#your-profile input[name="add_order"]',
        'form#your-profile input[name="yf_award"]',
        'form#your-profile input[name="yf_reward_monthly"]',
        'form#your-profile input[name="birthday_points"]',
        'form#your-profile input[name="clear_already_awarded"]',
        'form#your-profile input[name="yf_award_add"]',
        'form#your-profile textarea[name="yf_award_reason"]',
        'form#your-profile input[name="first_name"]',
        'form#your-profile input[name="nickname"]',
        'form#your-profile input[name="billing_phone"]',
        'form#your-profile input[name="email"]',
        'form#your-profile input[name="billing_address_1"]',
        'form#your-profile input[name="birthday"]',
        'form#your-profile input[name="gender"]',
    ];
    let selector_copy = [
        "form#your-profile tr#password",
        "form#your-profile  tr.user-pass2-wrap",
        "form#your-profile  tr.pw-weak",
        "form#your-profile  tr.user-generate-reset-link-wrap",
        "form#your-profile  tr.user_register_time",
        "form#your-profile  tr.user_reward_log",
    ];
    selector_copy = selector_copy.map((item) => document.querySelector(item));
    selector_copy = [...selector_inputHidden, ...selector_copy];
    selector_copy = selector_copy.filter((element) => {
        return element !== null;
    });
    selector_copy = selector_copy.map((item) => item.outerHTML);

    selector_copy_OuterTR = selector_copy_OuterTR.map((item) => document.querySelector(item));
    selector_copy_OuterTR = selector_copy_OuterTR.filter((element) => {
        return element !== null;
    });
    selector_copy_OuterTR = selector_copy_OuterTR.map((item) => item.closest("tr").outerHTML);

    const node_list_html = selector_copy_OuterTR.join("") + selector_copy.join("");

    const submit = document.querySelector("form#your-profile > p.submit").outerHTML;

    const html = `
    <table class="form-table" role="presentation">
		<tbody>
                 ${node_list_html}
		</tbody>
    </table>
    ${submit}
`;

    //get php_user_data from php
    console.log("user_member_lv_img_url", php_user_data.user_member_lv_img_url);
    const { user_member_lv_img_url } = php_user_data;

    //render
    const newUserEditForm = document.querySelector("#your-profile");
    newUserEditForm.innerHTML = html;
    newUserEditForm.style.cssText += ";display:block !important;";
    if (document.querySelector("#role") !== null) {
        document.querySelector("#role").classList.add("regular-text");
    }
    //用戶圖片
    document.querySelector(".user-profile-picture img.avatar").src = user_member_lv_img_url;
    document.querySelector(".user-profile-picture img.avatar").style.cssText += ";border: 8px solid #fff;";

    //事件
    document.querySelector("#first_name").addEventListener("change", (e) => {
        document.querySelector("#nickname").value = e.target.value;
    });
    document.querySelector("#force_modify_time_MemberLVexpire_date").addEventListener("click", (e) => {
        const input_time_MemberLVexpire_date = document.querySelector("#time_MemberLVexpire_date");
        input_time_MemberLVexpire_date.removeAttribute("disabled");
        input_time_MemberLVexpire_date.setAttribute("type", "date");

        if (input_time_MemberLVexpire_date.value == "無期限") {
            const d = new Date();
            input_time_MemberLVexpire_date.value =
                d.getFullYear() + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + d.getDate();
        }
    });

    /* const advance_setting_checkbox = document.querySelector("#advance_setting_checkbox")
    handle_advance_setting(advance_setting_checkbox.checked);
    advance_setting_checkbox.addEventListener("click", () => {
        handle_advance_setting(advance_setting_checkbox.checked);
    }); */
})();

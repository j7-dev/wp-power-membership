/** @type {import('tailwindcss').Config} */
module.exports = {
	content: ["./class/**/*.{php,js}", "./templates/**/*.{php,js}"],
	important: true,
	theme: {
		extend: {},
	},
	plugins: [],
	blocklist: ['fixed', 'col-1', 'col-2', 'hidden'], // 排除 class
}


/* eslint-disable @typescript-eslint/ban-ts-comment */
// @ts-nocheck

const APP_DOMAIN = 'power_membership_data' as string
export const snake = window?.[APP_DOMAIN]?.env?.SNAKE || 'power_membership'
export const appName = window?.[APP_DOMAIN]?.env?.APP_NAME || 'Power Membership'
export const kebab = window?.[APP_DOMAIN]?.env?.KEBAB || 'power-membership'
export const app1Selector = window?.[APP_DOMAIN]?.env?.APP1_SELECTOR || 'power_membership'
export const app2Selector =
	window?.[APP_DOMAIN]?.env?.APP2_SELECTOR || 'power_membership_metabox'
export const apiUrl = window?.wpApiSettings?.root || '/wp-json'
export const ajaxUrl =
	window?.[APP_DOMAIN]?.env?.ajaxUrl || '/wp-admin/admin-ajax.php'
export const siteUrl = window?.[APP_DOMAIN]?.env?.siteUrl || '/'
export const currentUserId = window?.[APP_DOMAIN]?.env?.userId || '0'
export const postId = window?.[APP_DOMAIN]?.env?.postId || '0'
export const permalink = window?.[APP_DOMAIN]?.env?.permalink || '/'
export const apiTimeout = '30000'
export const ajaxNonce = window?.[APP_DOMAIN]?.env?.nonce || ''

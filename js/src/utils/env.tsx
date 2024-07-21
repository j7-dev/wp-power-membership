/* eslint-disable @typescript-eslint/ban-ts-comment */
// @ts-nocheck

import { removeTrailingSlash } from 'antd-toolkit'

const APP_DOMAIN = 'power_membership_data' as string
export const snake = window?.[APP_DOMAIN]?.env?.SNAKE || 'power_membership'
export const appName =
	window?.[APP_DOMAIN]?.env?.APP_NAME || 'Power Membership'
export const kebab = window?.[APP_DOMAIN]?.env?.KEBAB || 'power-membership'
export const app1Selector =
	window?.[APP_DOMAIN]?.env?.APP1_SELECTOR || '#power_membership'
export const apiUrl =
	removeTrailingSlash(window?.wpApiSettings?.root) || '/wp-json'
export const ajaxUrl =
	removeTrailingSlash(window?.[APP_DOMAIN]?.env?.ajaxUrl) ||
	'/wp-admin/admin-ajax.php'

// export const siteUrl =
//   removeTrailingSlash(window?.[APP_DOMAIN]?.env?.siteUrl) || '/'

export const currentUserId = window?.[APP_DOMAIN]?.env?.userId || '0'
export const postId = window?.[APP_DOMAIN]?.env?.postId || '0'

// export const permalink =
//   removeTrailingSlash(window?.[APP_DOMAIN]?.env?.permalink) || '/'

export const apiTimeout = '30000'
export const ajaxNonce = window?.[APP_DOMAIN]?.env?.nonce || ''

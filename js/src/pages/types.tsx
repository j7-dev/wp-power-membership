import { TParamsBase } from '@/types'

export type DataType = {
  id: string
  title: string
  type: 'cron' | 'modify' | 'purchase'
  user_id: string
  modified_by: string
  date: string
  point_slug: 'power_money'
  point_changed: string
  new_balance: string
}

export type TLogExtraParams = {
  user_id?: string
  modified_by?: string
  type?: 'cron' | 'modify' | 'purchase'
}

export type TLogParams = TParamsBase & {
  user_id?: string
  modified_by?: string
  type?: 'cron' | 'modify' | 'purchase'
}

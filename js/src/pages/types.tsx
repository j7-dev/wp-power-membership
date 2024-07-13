import { TParamsBase } from '@/types'

type TLogType = 'cron' | 'modify' | 'purchase' | 'system' | 'manual'

export type DataType = {
  id: string
  title: string
  type: TLogType
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
  type?: TLogType
}

export type TLogParams = TParamsBase & {
  user_id?: string
  modified_by?: string
  type?: TLogType
}

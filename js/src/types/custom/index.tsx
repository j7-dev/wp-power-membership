export type TPagination = {
  total: number
  totalPages: number
  current: number
  pageSize: number
}

export type TParamsBase = {
  numberposts?: number
  offset?: number
  orderby?: string
  order?: 'ASC' | 'DESC'
}

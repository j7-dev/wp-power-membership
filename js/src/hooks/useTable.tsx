import { useState, useLayoutEffect } from 'react'
import { TParamsBase, TPagination } from '@/types'
import { UseQueryOptions, useQuery } from '@tanstack/react-query'
import { axios } from '@/api'
import { TableProps } from 'antd'

/*
 * T - params 的篩選屬性型別
 * K - table record 的資料型別
 */

export const useTable = <T, K>({
  resource,
  defaultParams,
  queryOptions,
}: {
  resource: string
  defaultParams: T
  queryOptions?: Omit<UseQueryOptions, 'queryKey'>
}) => {
  type TData = {
    data: K[]
		headers: {
			'x-wp-total': string
			'x-wp-totalpages': string
		}
  }

  const [params, setParams] = useState<T & TParamsBase>({
    ...defaultParams,
  } as T & TParamsBase)

  useLayoutEffect(() => {
    setParams({ ...defaultParams } as T & TParamsBase)
  }, [JSON.stringify(defaultParams)])

  const result = useQuery<T & TParamsBase, any, TData, any>({
    queryKey: [`${resource}`, JSON.stringify(params)] as any,
    queryFn: () => axios.get(`/${resource}`, { params }),
    ...queryOptions,
  })

  const handlePaginationChange = (page: number, pageSize: number) => {
    const offset = (page - 1) * (pageSize || 10)
    setParams({
      ...params,
      offset,
      numberposts: pageSize,
    })
  }

	const headers = result?.data?.headers

  const pagination = headers
    ? {
        total: Number(headers?.['x-wp-total']),
				totalPages: Number(headers?.['x-wp-totalpages']),
				// current: result?.data?.data?.data?.pagination.current_page,
        showSizeChanger: true,
        showTotal: (total: number) => `共 ${total} 筆`,
        onChange: handlePaginationChange,
        pageSizeOptions: [
          '10',
          '20',
          '50',
          '100',
        ],
      }
    : false

  const dataSource = result?.data?.data || []

  const tableProps: TableProps<K> = {
    loading: result?.isFetching,
    size: 'small',
    dataSource,
    pagination,
    scroll: { x: 860 },
  }

  return {
    tableProps,
    params,
    setParams,
    result,
  }
}

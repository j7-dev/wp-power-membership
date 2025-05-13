import React, { useState } from 'react'
import { Table, TableProps, DatePicker, DatePickerProps } from 'antd'
import { TLogRecord } from './types'
import dayjs, { Dayjs } from 'dayjs'
import { useQuery } from '@tanstack/react-query'
import { customAxios as axios } from '@/api'
import { AxiosResponse } from 'axios'

const columns: TableProps<TLogRecord>['columns'] = [
	{
		width: 160,
		title: '日期',
		dataIndex: 'date',
	},
	{
		width: 100,
		title: '獎勵原因',
		dataIndex: 'log_id',
		render: (_, record) => (
			<p className="m-0">
				#{record?.log_id} {record?.title}
			</p>
		),
	},
	{
		width: 100,
		align: 'right',
		title: '購物金變化',
		dataIndex: 'points',
	},

	// {
	// 	width: 160,
	// 	title: '分類',
	// 	dataIndex: 'type',
	// },

	{
		width: 160,
		align: 'right',
		title: '到期日',
		dataIndex: 'expiration_date',
	},
]

const disabledDate: DatePickerProps['disabledDate'] = (current) => {
	// Can not select days before today and today
	return current && current > dayjs().endOf('day')
}

const index = () => {
	// 預設日期為7天前
	const [date, setDate] = useState<Dayjs>(
		dayjs().subtract(7, 'day').startOf('day'),
	)

	const timestamp =
		date?.unix() || dayjs().subtract(7, 'day').startOf('day').unix()
	const { data, isLoading } = useQuery<AxiosResponse<TLogRecord[]>>({
		queryKey: ['logs', timestamp],
		queryFn: () => axios.get(`/logs?since=${timestamp}`),
		enabled: !!timestamp,
	} as any)

	const logs = data?.data || []
	return (
		<>
			<div className="mt-8 mb-4">
				<label className="mr-2 mb-2 text-sm">起始日期</label>
				<DatePicker
					defaultValue={date}
					onChange={setDate}
					disabledDate={disabledDate}
				/>
			</div>
			<Table
				rowKey="log_id"
				loading={isLoading}
				columns={columns}
				dataSource={logs}
				scroll={{ x: 600 }}
			/>
		</>
	)
}

export default index

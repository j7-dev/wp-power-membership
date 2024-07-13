import { Table, TableProps, Tag, Typography } from 'antd'
import { DataType, TLogParams } from './types'
import { useTable } from '@/hooks'
import { currentUserId, kebab } from '@/utils'

const { Paragraph } = Typography

// 使用 URL 对象解析 URL

const parsedUrl = new URL(window.location.href)

// 使用 URLSearchParams 获取查询参数

const params = new URLSearchParams(parsedUrl.search)

// 获取 user_id 参数值

const user_id = params.get('user_id') ?? currentUserId

const LogTypeTag: React.FC<{ record: DataType }> = ({ record }) => {
	const type = record?.type || ''
	switch (type) {
		case 'cron':
			return <Tag color="purple">每日扣點</Tag>
		case 'manual':
		case 'modify':
			return <Tag color="magenta">管理員修改</Tag>
		case 'purchase':
			return <Tag color="cyan">儲值</Tag>
		case 'system':
			return <Tag color="blue">系統</Tag>
		default:
			return <Tag>未分類</Tag>
	}
}

function DefaultPage() {
	const { tableProps } = useTable<TLogParams, DataType>({
		resource: `${kebab}/logs`,
		defaultParams: {
			user_id,
		},
		queryOptions: {
			enabled: !!user_id,
		},
	})

	// const { data, isLoading } = useMany({
	//   resource: 'logs',
	//   dataProvider: 'power-membership',
	//   args: {
	//     user_id: currentUserId,
	//   },
	//   config: {
	//     headers: {
	//       Authorization: `Basic ${token}`,
	//     },
	//   },
	// })

	const columns: TableProps<DataType>['columns'] = [
		{
			title: <p className='m-0 text-center'>日期</p>,
			dataIndex: 'date',
			width: 160,
		},
		{
			title: <p className='m-0 text-center'>分類</p>,
			dataIndex: 'type',
			width: 120,
			render: (_, record) => <LogTypeTag record={record} />,
		},
		{
			title: <p className='m-0 text-center'>購物金項目</p>,
			dataIndex: 'title',
			width: 200,
			render: (value: string) => (
				<Paragraph
					copyable
					ellipsis={{
						rows: 2,
						expandable: true,
						symbol: '更多',
					}}
					className="whitespace-break-spaces m-0"
				>
					{value}
				</Paragraph>
			),
		},
		{
			title: <p className='m-0 text-center'>購物金款項</p>,
			dataIndex: 'point_changed',
			width: 120,
			align: 'right',
			render: (v: string) => Number(v) > 0 ? <span className='text-green-500'>+{v}</span> : <span className='text-red-500'>{v}</span>,
		},
		{
			title: <p className='m-0 text-center'>到期日</p>,
			dataIndex: 'expire_date',
			width: 120,
			align: 'right',
		},
		{
			title: <p className='m-0 text-center'>購物金款項</p>,
			dataIndex: 'point_changed',
			width: 120,
			align: 'right',
		},
		{
			title: <p className='m-0 text-center'>餘額</p>,
			dataIndex: 'new_balance',
			width: 120,
			align: 'right',
		},

	]

	return <Table rowKey="id" {...tableProps} columns={columns} size="small" scroll={{
		x: 960,
	}} />
}

export default DefaultPage

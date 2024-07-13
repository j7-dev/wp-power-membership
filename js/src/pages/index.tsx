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
			return <Tag color="magenta">管理員直接修改</Tag>
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
			title: '日期',
			dataIndex: 'date',
			width: 160,
		},
		{
			title: '分類',
			dataIndex: 'type',
			width: 144,
			render: (_, record) => <LogTypeTag record={record} />,
		},
		{
			title: '點數變化',
			dataIndex: 'point_changed',
			width: 144,
			align: 'right',
		},
		{
			title: '餘額',
			dataIndex: 'new_balance',
			width: 208,
			align: 'right',
		},
		{
			title: '說明',
			dataIndex: 'title',
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
	]

	return <Table rowKey="id" {...tableProps} columns={columns} />
}

export default DefaultPage

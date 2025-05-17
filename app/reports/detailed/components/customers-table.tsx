"use client"

import { useState } from "react"
import {
  type ColumnDef,
  type ColumnFiltersState,
  type SortingState,
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
} from "@tanstack/react-table"
import { ArrowUpDown, ChevronDown } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Skeleton } from "@/components/ui/skeleton"
import { Badge } from "@/components/ui/badge"

interface CustomersData {
  id: string
  name: string
  email: string
  phone: string
  website: string
  orders_count: number
  total_spent: number
  last_order: string
  customer_type: string
}

const data: CustomersData[] = [
  {
    id: "1",
    name: "Nguyễn Văn A",
    email: "nguyenvana@example.com",
    phone: "0901234567",
    website: "Shop Thời Trang",
    orders_count: 5,
    total_spent: 3250000,
    last_order: "15/05/2023",
    customer_type: "returning",
  },
  {
    id: "2",
    name: "Trần Thị B",
    email: "tranthib@example.com",
    phone: "0912345678",
    website: "Shop Điện Tử",
    orders_count: 2,
    total_spent: 5800000,
    last_order: "14/05/2023",
    customer_type: "new",
  },
  {
    id: "3",
    name: "Lê Văn C",
    email: "levanc@example.com",
    phone: "0923456789",
    website: "Shop Mỹ Phẩm",
    orders_count: 8,
    total_spent: 4500000,
    last_order: "12/05/2023",
    customer_type: "returning",
  },
  {
    id: "4",
    name: "Phạm Thị D",
    email: "phamthid@example.com",
    phone: "0934567890",
    website: "Shop Thời Trang",
    orders_count: 3,
    total_spent: 2800000,
    last_order: "10/05/2023",
    customer_type: "returning",
  },
  {
    id: "5",
    name: "Hoàng Văn E",
    email: "hoangvane@example.com",
    phone: "0945678901",
    website: "Shop Điện Tử",
    orders_count: 1,
    total_spent: 2800000,
    last_order: "08/05/2023",
    customer_type: "new",
  },
  {
    id: "6",
    name: "Ngô Thị F",
    email: "ngothif@example.com",
    phone: "0956789012",
    website: "Shop Mỹ Phẩm",
    orders_count: 4,
    total_spent: 2500000,
    last_order: "05/05/2023",
    customer_type: "returning",
  },
  {
    id: "7",
    name: "Đặng Văn G",
    email: "dangvang@example.com",
    phone: "0967890123",
    website: "Shop Thời Trang",
    orders_count: 6,
    total_spent: 4200000,
    last_order: "03/05/2023",
    customer_type: "returning",
  },
  {
    id: "8",
    name: "Vũ Thị H",
    email: "vuthih@example.com",
    phone: "0978901234",
    website: "Shop Điện Tử",
    orders_count: 2,
    total_spent: 6500000,
    last_order: "01/05/2023",
    customer_type: "new",
  },
  {
    id: "9",
    name: "Bùi Văn I",
    email: "buivani@example.com",
    phone: "0989012345",
    website: "Shop Mỹ Phẩm",
    orders_count: 3,
    total_spent: 1850000,
    last_order: "28/04/2023",
    customer_type: "returning",
  },
  {
    id: "10",
    name: "Lý Thị K",
    email: "lythik@example.com",
    phone: "0990123456",
    website: "Shop Thời Trang",
    orders_count: 1,
    total_spent: 1350000,
    last_order: "25/04/2023",
    customer_type: "new",
  },
]

const columns: ColumnDef<CustomersData>[] = [
  {
    accessorKey: "name",
    header: "Tên khách hàng",
    cell: ({ row }) => <div className="font-medium">{row.getValue("name")}</div>,
  },
  {
    accessorKey: "email",
    header: "Email",
    cell: ({ row }) => <div>{row.getValue("email")}</div>,
  },
  {
    accessorKey: "phone",
    header: "Số điện thoại",
    cell: ({ row }) => <div>{row.getValue("phone")}</div>,
  },
  {
    accessorKey: "website",
    header: "Website",
    cell: ({ row }) => <div>{row.getValue("website")}</div>,
  },
  {
    accessorKey: "orders_count",
    header: ({ column }) => {
      return (
        <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}>
          Số đơn hàng
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
    cell: ({ row }) => <div className="text-right">{row.getValue("orders_count")}</div>,
  },
  {
    accessorKey: "total_spent",
    header: ({ column }) => {
      return (
        <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}>
          Tổng chi tiêu
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
    cell: ({ row }) => {
      const amount = Number.parseFloat(row.getValue("total_spent"))
      const formatted = new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        maximumFractionDigits: 0,
      }).format(amount)

      return <div className="text-right font-medium">{formatted}</div>
    },
  },
  {
    accessorKey: "last_order",
    header: "Đơn hàng gần nhất",
    cell: ({ row }) => <div>{row.getValue("last_order")}</div>,
  },
  {
    accessorKey: "customer_type",
    header: "Loại khách hàng",
    cell: ({ row }) => {
      const type = row.getValue("customer_type") as string
      return (
        <Badge className={type === "returning" ? "bg-blue-500" : type === "new" ? "bg-green-500" : ""}>
          {type === "returning" ? "Khách hàng cũ" : type === "new" ? "Khách hàng mới" : type}
        </Badge>
      )
    },
  },
]

interface CustomersTableProps {
  isLoading?: boolean
}

export function CustomersTable({ isLoading = false }: CustomersTableProps) {
  const [sorting, setSorting] = useState<SortingState>([])
  const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([])
  const [columnVisibility, setColumnVisibility] = useState({})

  const table = useReactTable({
    data,
    columns,
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onColumnVisibilityChange: setColumnVisibility,
    state: {
      sorting,
      columnFilters,
      columnVisibility,
    },
  })

  if (isLoading) {
    return (
      <div className="w-full">
        <div className="flex items-center py-4">
          <Skeleton className="h-10 w-[250px]" />
          <Skeleton className="h-10 w-[110px] ml-auto" />
        </div>
        <div className="rounded-md border">
          <Skeleton className="h-[400px] w-full" />
        </div>
        <div className="flex items-center justify-end space-x-2 py-4">
          <Skeleton className="h-10 w-[100px]" />
          <Skeleton className="h-10 w-[70px]" />
        </div>
      </div>
    )
  }

  return (
    <div className="w-full">
      <div className="flex items-center py-4">
        <Input
          placeholder="Tìm kiếm theo tên khách hàng..."
          value={(table.getColumn("name")?.getFilterValue() as string) ?? ""}
          onChange={(event) => table.getColumn("name")?.setFilterValue(event.target.value)}
          className="max-w-sm"
        />
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="outline" className="ml-auto">
              Cột <ChevronDown className="ml-2 h-4 w-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            {table
              .getAllColumns()
              .filter((column) => column.getCanHide())
              .map((column) => {
                return (
                  <DropdownMenuCheckboxItem
                    key={column.id}
                    className="capitalize"
                    checked={column.getIsVisible()}
                    onCheckedChange={(value) => column.toggleVisibility(!!value)}
                  >
                    {column.id === "name"
                      ? "Tên khách hàng"
                      : column.id === "email"
                        ? "Email"
                        : column.id === "phone"
                          ? "Số điện thoại"
                          : column.id === "website"
                            ? "Website"
                            : column.id === "orders_count"
                              ? "Số đơn hàng"
                              : column.id === "total_spent"
                                ? "Tổng chi tiêu"
                                : column.id === "last_order"
                                  ? "Đơn hàng gần nhất"
                                  : column.id === "customer_type"
                                    ? "Loại khách hàng"
                                    : column.id}
                  </DropdownMenuCheckboxItem>
                )
              })}
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
      <div className="rounded-md border">
        <Table>
          <TableHeader>
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id}>
                {headerGroup.headers.map((header) => {
                  return (
                    <TableHead key={header.id}>
                      {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                    </TableHead>
                  )
                })}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows?.length ? (
              table.getRowModel().rows.map((row) => (
                <TableRow key={row.id} data-state={row.getIsSelected() && "selected"}>
                  {row.getVisibleCells().map((cell) => (
                    <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={columns.length} className="h-24 text-center">
                  Không tìm thấy kết quả.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>
      <div className="flex items-center justify-end space-x-2 py-4">
        <div className="flex-1 text-sm text-muted-foreground">
          Hiển thị {table.getRowModel().rows.length} trong tổng số {data.length} dòng.
        </div>
        <div className="space-x-2">
          <Button
            variant="outline"
            size="sm"
            onClick={() => table.previousPage()}
            disabled={!table.getCanPreviousPage()}
          >
            Trước
          </Button>
          <Button variant="outline" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
            Sau
          </Button>
        </div>
      </div>
    </div>
  )
}
